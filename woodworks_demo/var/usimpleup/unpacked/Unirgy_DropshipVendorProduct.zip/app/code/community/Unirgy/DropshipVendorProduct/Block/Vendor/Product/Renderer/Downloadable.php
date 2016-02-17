<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_Downloadable extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/downloadable.phtml');
    }
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
    protected function _toHtml()
    {
        $accordion = $this->getLayout()->createBlock('udprod/vendor_product_renderer_widget_accordion')
            ->setId('downloadableInfo');

        $accordion->addItem('samples', array(
            'title'   => Mage::helper('udropship')->__('Samples'),
            'content' => $this->getLayout()
                ->createBlock('udprod/vendor_product_renderer_downloadable_samples')->toHtml(),
            'open'    => false,
        ));

        $accordion->addItem('links', array(
            'title'   => Mage::helper('udropship')->__('Links'),
            'content' => $this->getLayout()->createBlock(
                'udprod/vendor_product_renderer_downloadable_links',
                'catalog.product.edit.tab.downloadable.links')->toHtml(),
            'open'    => true,
        ));

        $this->setChild('accordion', $accordion);

        return Mage_Core_Block_Template::_toHtml();
    }
}