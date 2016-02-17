<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Gallery extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{
    public function getContentHtml()
    {
        /* @var $content Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content */
        if (Mage::registry('current_product')->getTypeId()=='configurable') {
            $content = Mage::getSingleton('core/layout')->createBlock('udprod/vendor_product_galleryCfgContentExs');
        } else {
            $content = Mage::getSingleton('core/layout')->createBlock('udprod/vendor_product_galleryContent');
        }

        $content->setId($this->getHtmlId() . '_content')
            ->setElement($this);
        return $content->toHtml();
    }
    public function setValue($value)
    {
        parent::setValue($value);
        return $this;
    }
}