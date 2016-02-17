<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_Downloadable_Links extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/downloadable/links.phtml');
    }
    public function getConfigJson($type='links')
    {
        $this->getConfig()->setUrl(
            Mage::getModel('core/url')->addSessionParam()
                ->getUrl('udprod/vendor/downloadableUpload', array('type' => $type))
        );
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField($type);
        $this->getConfig()->setFilters(array(
            'all'    => array(
                'label' => Mage::helper('udropship')->__('All Files'),
                'files' => array('*.*')
            )
        ));
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setWidth('32');
        $this->getConfig()->setHideUploadButton(true);
        return Mage::helper('core')->jsonEncode($this->getConfig()->getData());
    }
}