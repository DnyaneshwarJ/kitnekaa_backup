<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_VendorEditTab_ComRates_Renderer_Rates extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('udtiercom/vendor/helper/category_rates_config.phtml');
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

    public function getTopCategories()
    {
        return Mage::helper('udtiercom')->getTopCategories();
    }

    public function getTiercomRates()
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

    public function getGlobalTierComConfig()
    {
        $value = Mage::getStoreConfig('udropship/tiercom/rates', $this->getStore());
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getStore()
    {
        if ($this->getElement()->getStore()) {
            return $this->getElement()->getStore();
        }
        return Mage_Core_Model_App::ADMIN_STORE_ID;
    }
}