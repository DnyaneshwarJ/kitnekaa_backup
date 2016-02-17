<?php

class Unirgy_DropshipVendorProduct_Block_ProductViewTypeConfigurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                //if ($product->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_DISABLED) $products[] = $product;
                if ($product->isSaleable() || $this->getProduct()->getIsProductListFlag()) $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('udprod/session');
    }

    protected function attachChildProducts(&$config)
    {
        $childProducts = array();

        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
            );

        }

        $config['childProducts'] = $childProducts;

        return $this;
    }

    public function getJsonConfig()
    {
        $config = Mage::helper('core')->jsonDecode(parent::getJsonConfig());
        $this->attachChildProducts($config);
        $config['template'] = str_replace('%s', '#{price}', $this->getCurrencyOutputFormat());
        $config['productStock'] = array();
        foreach ($this->getAllowProducts() as $simple) {
            $config['productStock'][$simple->getId()] = $simple->isSaleable() ? $simple->getStockItem()->getQty() : 0;
        }
        $i=1;
        foreach ($this->getAllowAttributes() as $cfgAttr) {
            $_aId = $cfgAttr->getAttributeId();
            if (!isset($config['attributes'][$_aId])) continue;
            $config['attributes'][$_aId]['idx'] = $i++;
            $config['attributes'][$_aId]['identifyImage'] = $cfgAttr->getData('identify_image');
            $_sk = sprintf('super_attribute/%s/%s', $this->getProduct()->getId(), $cfgAttr->getAttributeId());
            $_pAttr = $cfgAttr->getProductAttribute();
            $config['perAttrChooseText'][$_aId] = Mage::helper('udropship')->__('- Select %s -', $_pAttr->getFrontend()->getLabel());
            $_swatchMap = $_pAttr->getSwatchMap();
            if (!is_array($_swatchMap)) {
                $_swatchMap = (array)Mage::helper('core')->jsonDecode($_swatchMap);
            }
            if (!empty($config['attributes'][$_aId]['options'])) {
                foreach ($config['attributes'][$_aId]['options'] as &$opt) {
                    if (isset($_swatchMap[$opt['id']])) {
                        $opt['swatch'] = sprintf('%s/catalog/swatch/%s/%s', Mage::getBaseUrl('media'), $_aId, $_swatchMap[$opt['id']]);
                    } else {
                        $opt['swatch'] = Mage::getDesign()->getSkinUrl('images/fpo/ph_swatch.gif');
                    }
                }
                unset($opt);
                if ($config['attributes'][$_aId]['idx']==1) {
                    $newOptions = array();
                    $usedValues = array();
                    $simpleProducts = $this->getAllowProducts();
                    foreach ($simpleProducts as $simpleProd) {
                        $usedValues[] = $simpleProd->getData($_pAttr->getAttributeCode());
                    }
                    $usedValues = array_unique($usedValues);
                    $defColor = $this->getDefaultColorByImage();
                    foreach ($config['attributes'][$_aId]['options'] as $opt) {
                        foreach ($usedValues as $usedValue) {
                            if ($opt['id'] == $usedValue) {
                                if ($defColor==$opt['id']) {
                                    array_unshift($newOptions, $opt);
                                } else {
                                    $newOptions[] = $opt;
                                }
                            }
                        }
                    }
                    $config['attributes'][$_aId]['options'] = $newOptions;
                }

            }
            if ($this->_getSession()->getData($_sk)) {
                $config['attributes'][$_aId]['defaultValueId'] = $this->_getSession()->getData($_sk);
                $config['attributes'][$_aId]['isColor'] = $_pAttr->getAttributeCode() == 'color';
            } 
        }
        $this->_getSession()->unsetData('super_attribute');
        return Mage::helper('core')->jsonEncode($config);
    }
    
    public function getCurrencyOutputFormat()
    {
        $store = Mage::app()->getStore();
        $formated = $store->getCurrentCurrency()->formatTxt(0, array('precision' => 0));
        $number = $store->getCurrentCurrency()->formatTxt(0, array('display'=>Zend_Currency::NO_SYMBOL, 'precision' => 0));
        return str_replace($number, '%s', $formated);
    }
    
    public function getDefaultColorByImage()
    {
        return Mage::helper('udprod')->getDefaultColorByImage($this->getProduct());
    }
    
    public function getFinalPrice()
    {
        $store = Mage::app()->getStore();
        return $store->getCurrentCurrency()->formatPrecision($this->getProduct()->getFinalPrice(), 0);
    }
    
    public function getAttributeId($code)
    {
        return Mage::getSingleton('eav/config')->getAttribute('catalog_product', $code)->getId();
    }
    
}
