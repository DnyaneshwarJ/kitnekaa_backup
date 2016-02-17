<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_GalleryCfgContentExs extends Unirgy_DropshipVendorProduct_Block_Vendor_Product_GalleryContent
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/cfg_gallery_exs.phtml');
    }
    public function getProduct()
    {
        return Mage::registry('product')
            ? Mage::registry('product')
            : Mage::registry('current_product');
    }

    public function getCfgAttribute()
    {
        return Mage::helper('udprod')->getCfgFirstAttribute($this->getProduct());
    }

    public function getCfgFirstAttributeOptions()
    {
        $values = array();
        $_values = Mage::helper('udprod')->getCfgFirstAttributeValues(
            $this->getProduct(),
            true
        );
        foreach ($_values as $_val) {
            $values[] = $_val['value'];
        }
        return $values;
    }

    protected $_iiAttrs;
    public function getIdentifyImageAttributes()
    {
        if (is_null($this->_iiAttrs)) {
            $this->_iiAttrs = array();
            $p = $this->getProduct();
            foreach ($p->getTypeInstance(true)->getConfigurableAttributes($p) as $cfgAttr) {
                if ($cfgAttr->getIdentifyImage()) {
                    $this->_iiAttrs[] = $cfgAttr;
                    $availableValues = array();
                    $cPrices = $cfgAttr->getPrices();
                    if (!empty($cPrices)) {
                        foreach ($cfgAttr->getPrices() as $prEntry) {
                            $availableValues[$prEntry['value_index']] = $prEntry['label'];
                        }
                    }
                    $cfgAttr->setAvailableValues($availableValues);
                }
            }
        }
        return $this->_iiAttrs;
    }

    public function getIdentifyImageAttributesJson()
    {
        $iiAttrs = array();
        $p = $this->getProduct();
        foreach ($p->getTypeInstance()->getConfigurableAttributesAsArray($p) as $cfgAttr) {
            if ($cfgAttr['identify_image']) {
                $iiAttrs[] = $cfgAttr;
            }
        }
        return Mage::helper('core')->jsonEncode($iiAttrs);
    }

    public function getImagesData()
    {
        $perOptionHidden = Mage::getSingleton('udprod/source')->isMediaCfgPerOptionHidden();
        if(is_array($this->getProduct()->getMediaGallery())) {
            $value = $this->getProduct()->getMediaGallery();
            if(count($value['images'])>0) {
                $images = array();
                $_images = $value['images'];
                try {
                    $usedValues = $this->getCfgFirstAttributeOptions();
                    $cfgAttrId = $this->getCfgAttribute()->getId();
                    foreach ($_images as $image) {
                        if ($perOptionHidden
                            || !isset($image['super_attribute'])
                            || !isset($image['super_attribute'][$cfgAttrId])
                            || !in_array($image['super_attribute'][$cfgAttrId], $usedValues)
                        ) {
                            $image['url'] = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($image['file']);
                            $image['main'] = @$image['super_attribute']['main'];
                            $images[] = $image;
                        }
                    }
                } catch (Exception $e) {
                    var_dump($value);
                    die("$e");
                }
                return $images;
            }
        }
        return array();
    }

    public function getImagesJson()
    {
        return Mage::helper('core')->jsonEncode($this->getImagesData());
    }
}