<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_SystemConfigField_Rates extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiercom/system/form_field/category_rates_config.phtml');
        }
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        return $html;
    }

    public function getTopCategories()
    {
        return Mage::helper('udtiercom')->getTopCategories();
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }
}