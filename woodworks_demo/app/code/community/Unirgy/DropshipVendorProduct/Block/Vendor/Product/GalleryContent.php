<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_GalleryContent extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/gallery.phtml');
    }
    protected function _prepareLayout()
    {
        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }
    protected $_uploader;
    public function getUploader()
    {
        if (null === $this->_uploader) {
            $url = Mage::getModel('core/url')->addSessionParam()
                ->getUrl('udprod/vendor/upload', array('image_field'=>'image'));
            $this->_uploader = $this->getLayout()->createBlock('udprod/vendor_product_uploader');
            $this->_uploader->getConfig()
                ->setUrl($url)
                ->setFileField('image')
                ->setFilters(array(
                    'images' => array(
                        'label' => Mage::helper('udropship')->__('Images (.gif, .jpg, .png)'),
                        'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                    )
                ));
        }
        return $this->_uploader;
    }
    public function getUploaderHtml()
    {
        return $this->getUploader()->toHtml();
    }
    public function getImageTypes()
    {
        $imageTypes = array();
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $imageTypes[$attribute->getAttributeCode()] = array(
                'label' => $attribute->getFrontend()->getLabel(),
                'field' => $this->getElement()->getAttributeFieldName($attribute)
            );
        }
        return $imageTypes;
    }

    public function hasUseDefault()
    {
        return false;
    }
}