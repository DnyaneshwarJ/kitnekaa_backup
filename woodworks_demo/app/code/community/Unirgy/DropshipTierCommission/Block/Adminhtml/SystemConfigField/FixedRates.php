<?php

class Unirgy_DropshipTierCommission_Block_Adminhtml_SystemConfigField_FixedRates extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiercom/system/form_field/fixed/rates_config.phtml');
        }
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        return $html;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('udropship')->__('Delete'),
                    'class' => 'delete delete-option'
                )));
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('udropship')->__('Add'),
                    'class' => 'add',
                    'id'    => 'udtcFixed_config_add_new_option_button'
                )));
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
}