<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_Downloadable_Samples extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Samples
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/downloadable/samples.phtml');
    }
    public function getConfigJson()
    {
        $this->getConfig()->setUrl(
            Mage::getModel('core/url')->addSessionParam()
                ->getUrl('udprod/vendor/downloadableUpload', array('type' => 'samples'))
        );
        $this->getConfig()->setParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileField('samples');
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