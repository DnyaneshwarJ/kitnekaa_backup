<?php


class Unirgy_DropshipMulti_Block_Vendor_ProductAttribute_Form_GroupPrice extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $this->setData('__hide_label',1);
        $html = $this->getHtml();
        $this->setData('__hide_label',0);
        return $html;
    }
    public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('udmulti/vendor_productAttribute_renderer_groupPrice');
        return parent::getHtml();
    }
}