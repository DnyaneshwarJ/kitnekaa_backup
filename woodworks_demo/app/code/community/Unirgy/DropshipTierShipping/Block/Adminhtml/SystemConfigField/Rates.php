<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_SystemConfigField_Rates extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiership/system/form_field/category_rates_config.phtml');
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
        return Mage::helper('udtiership')->getTopCategories();
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
        switch (current($subkeyColumns)) {
            case 'cost':
                $title = Mage::helper('udropship')->__('Cost for the first item');
                break;
            case 'additional':
                $title = Mage::helper('udropship')->__('Additional item cost');
                break;
            case 'handling':
                $title = Mage::helper('udropship')->__('Tier handling fee');
                break;
        }
        return $title;
    }

    public function isShowAdditionalColumn()
    {
        return Mage::helper('udtiership')->useAdditional($this->getStore());
    }

    public function isShowHandlingColumn()
    {
        return Mage::helper('udtiership')->useHandling($this->getStore());
    }

}