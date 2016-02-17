<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_Widget_Accordion extends Mage_Adminhtml_Block_Widget_Accordion
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/widget/accordion.phtml');
    }
    public function addItem($itemId, $config)
    {
        $this->_items[$itemId] = $this->getLayout()->createBlock('udprod/vendor_product_renderer_widget_accordion_item')
            ->setData($config)
            ->setAccordion($this)
            ->setId($itemId);
        if (isset($config['content']) && $config['content'] instanceof Mage_Core_Block_Abstract) {
            $this->_items[$itemId]->setChild($itemId.'_content', $config['content']);
        }

        $this->setChild($itemId, $this->_items[$itemId]);
        return $this;
    }
}