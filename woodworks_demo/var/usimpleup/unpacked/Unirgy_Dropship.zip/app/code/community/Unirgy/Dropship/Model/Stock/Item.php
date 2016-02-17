<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
    * Should we make Magento think that the product is in stock,
    * when dropship vendors are configured to have unlimited stock?
    *
    * Inactive during udropship calculations logic to get true picture
    * Active all other times to fool Magento into thinking that the product is in stock
    *
    * @return boolean
    */
    public function getAlwaysInStock()
    {
        $hlp = Mage::helper('udropship');
        $availability = Mage::getSingleton('udropship/stock_availability');
        $store = Mage::app()->getStore();

        if (!$hlp->isActive($store) || !$availability->getUseLocalStockIfAvailable($store) || $availability->getTrueStock()) {
            return false;
        }

        $productVendor = $this->getUdropshipVendor();

        $result = $productVendor && ($productVendor != $hlp->getLocalVendorId($store));
        return $result;
    }

    public function getManageStock()
    {
        $isMP = Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorProduct');
        if (!$isMP) {
            return parent::getManageStock();
        }
        if ($this->getUseConfigManageStock()) {
            $productVendor = $this->getUdropshipVendor();
            return $productVendor
                ? (int) Mage::helper('udropship')->getVendorUseCustomFallbackField($productVendor, 'is_udprod_manage_stock', 'udprod_manage_stock', self::XML_PATH_MANAGE_STOCK)
                : (int) Mage::getStoreConfigFlag(self::XML_PATH_MANAGE_STOCK);
        }
        return $this->getData('manage_stock');
    }

    public function getIsInStock()
    {
        $result = $this->getAlwaysInStock() || parent::getIsInStock();
        Mage::dispatchEvent('udropship_stock_item_getIsInStock', array('item'=>$this, 'vars'=>array('result'=>&$result)));
        return $result;
    }

    public function checkQty($qty)
    {
        $result = $this->getAlwaysInStock() || parent::checkQty($qty);
        Mage::dispatchEvent('udropship_stock_item_checkQty', array('item'=>$this, 'vars'=>array('result'=>&$result), 'qty'=>$qty));
        return $result;
    }

    public function getQty()
    {
        $qty = $this->getData('qty');#$this->getAlwaysInStock() ? 999999999 : $this->getData('qty');
        Mage::dispatchEvent('udropship_stock_item_getQty', array('item'=>$this, 'vars'=>array('qty'=>&$qty)));
        return $qty;
    }

    public function getBackorders()
    {
        $isMP = Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorProduct');
        if (!$isMP) {
            $backorders = parent::getBackorders();
        } else {
            $backorders = $this->getData('backorders');
            if ($this->getUseConfigBackorders()) {
                $productVendor = $this->getUdropshipVendor();
                $backorders = $productVendor
                    ? (int) Mage::helper('udropship')->getVendorUseCustomFallbackField($productVendor, 'is_udprod_backorders', 'udprod_backorders', self::XML_PATH_BACKORDERS)
                    : (int) Mage::getStoreConfigFlag(self::XML_PATH_BACKORDERS);
            }
        }
        Mage::dispatchEvent('udropship_stock_item_getBackorders', array('item'=>$this, 'vars'=>array('backorders'=>&$backorders)));
        return $backorders;
    }

    public function getMinQty()
    {
        $isMP = Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorProduct');
        $minQty = parent::getMinQty();
        if ($isMP && $this->getUseConfigMinQty()
            && ($productVendor = $this->getUdropshipVendor())
            && ($v = Mage::helper('udropship')->getVendor($productVendor))
            && $v->getId()
            && $v->getData('is_udprod_min_qty')
        ) {
            $minQty = (float)$v->getData('udprod_min_qty');
        }
        return $minQty;
    }

    public function getMaxSaleQty()
    {
        $isMP = Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorProduct');
        $maxSaleQty = parent::getMaxSaleQty();
        if ($isMP && $this->getUseConfigMaxSaleQty()
            && ($productVendor = $this->getUdropshipVendor())
            && ($v = Mage::helper('udropship')->getVendor($productVendor))
            && $v->getId()
            && $v->getData('is_udprod_max_sale_qty')
        ) {
            $maxSaleQty = (float)$v->getData('udprod_max_sale_qty');
        }
        return $maxSaleQty;
    }

    public function getMinSaleQty()
    {
        $isMP = Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorProduct');
        $minSaleQty = parent::getMinSaleQty();
        if ($isMP && $this->getUseConfigMinSaleQty()
            && ($productVendor = $this->getUdropshipVendor())
            && ($v = Mage::helper('udropship')->getVendor($productVendor))
            && $v->getId()
            && $v->getData('is_udprod_min_sale_qty')
        ) {
            $minSaleQty = (float)$v->getData('udprod_min_sale_qty');
        }
        return $minSaleQty;
    }

    public function assignProduct(Mage_Catalog_Model_Product $product)
    {
        parent::assignProduct($product);

        if ($this->getAlwaysInStock()) {
            $product->setIsSalable(true);
        }

        return $this;
    }

    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {
        $result = parent::checkQuoteItemQty($qty, $summaryQty, $origQty);
        if ($this->getAlwaysInStock()) {
            $result->setItemBackorders(0);
        }
        Mage::dispatchEvent('udropship_stock_item_checkQuoteItemQty', array('item'=>$this, 'vars'=>array('result'=>&$result)));
        return $result;
    }

    /*
    public function getProductObject()
    {
        if ($this->getProductId() && !$this->getData('product_object')) {
            $this->setData('product_object', Mage::getModel('catalog/product')->load($this->getProductId()));
        }
        return $this->getData('product_object');
    }
    */

    // override is required, since Magento 1.4.0.1 removed the event cataloginventory_stock_item_save_before
    protected function _beforeSave()
    {
        parent::_beforeSave();

        Mage::dispatchEvent('udropship_stock_item_save_before', array('item' => $this));
    }

    public function verifyStock($qty = null)
    {
        $result = parent::verifyStock($qty);
        Mage::dispatchEvent('udropship_stock_item_verifyStock', array('item'=>$this, 'qty'=>$qty, 'vars'=>array('result'=>&$result)));
        return $result;
    }

    public function verifyNotification($qty = null)
    {
        $result = parent::verifyNotification($qty);
        Mage::dispatchEvent('udropship_stock_item_verifyNotification', array('item'=>$this, '$qty'=>$qty, 'vars'=>array('result'=>&$result)));
        return $result;
    }

    public function canSubtractQty()
    {
        $hlp = Mage::helper('udropship');
        $obs = Mage::getSingleton('udropship/observer');
        if ($this->getAlwaysInStock()) {
            $localVendorId = $hlp->getLocalVendorId();
            if (($item = $obs->getOrderItem())) {
                if ($item->getUdropshipVendor()!=$localVendorId) {
                    return false;
                }
            } elseif (($quote = $obs->getQuote())) {
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getProductId()==$this->getProductId() && $item->getUdropshipVendor()!=$localVendorId) {
                        return false;
                    }
                }
            }
        }
        return $hlp->hasMageFeature('stock_can_subtract_qty') ? parent::canSubtractQty() : true;
    }

    public function subtractQty($qty)
    {
        if (Mage::helper('udropship')->hasMageFeature('stock_can_subtract_qty') || $this->canSubtractQty()) {
            return parent::subtractQty($qty);
        }
        return $this;
    }

    protected $_udropshipVendor;
    public function getUdropshipVendor()
    {
        $productVendor = null;
        if ($this->getProduct()) {
            $productVendor = $this->getProduct()->getUdropshipVendor();
        } elseif ($this->_udropshipVendor) {
            $productVendor = $this->_udropshipVendor;
        } else {
            $store = Mage::app()->getStore();
            $res = Mage::getSingleton('core/resource');
            $eav = Mage::getSingleton('eav/config');
            $read = $res->getConnection('catalog_read');
            $udvAttr = $eav->getAttribute('catalog_product', 'udropship_vendor');
            $select = $read->select()
                ->from($udvAttr->getBackend()->getTable(), array('value'))
                ->where('attribute_id=?', $udvAttr->getAttributeId())
                ->where('entity_id=?', $this->getProductId())
                ->where('store_id in (0, ?)', $store->getId())
                ->order('store_id', 'desc');
            $productVendor = $read->fetchOne($select);
            $this->_udropshipVendor = $productVendor;
        }
        return $productVendor;
    }

    public function getProduct()
    {
        return $this->_productInstance;
    }
}