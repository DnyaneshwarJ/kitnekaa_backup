<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_VendorEditTab_ShippingRates_Renderer_SimpleRates extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('udtiership/vendor/helper/simple_rates_config.phtml');
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

    public function getTiershipSimpleRates()
    {
        $value = $this->_element->getValue();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getGlobalTierShipConfigSimple()
    {
        $value = Mage::getStoreConfig('carriers/udtiership/simple_rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }

    public function getColumnTitle($subkeyColumns, $idx)
    {
        reset($subkeyColumns);
        $i=0; while ($i++!=$idx) next($subkeyColumns);
        $title = '';
        $column = current($subkeyColumns);
        switch ($column[1]) {
            case 'cost':
                $title = Mage::helper('udropship')->__('Cost for the first item');
                break;
            case 'additional':
                $title = Mage::helper('udropship')->__('Additional item cost');
                break;
        }
        return $title;
    }

}