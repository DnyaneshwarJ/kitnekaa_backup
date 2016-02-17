<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_SystemConfigField_FieldsetsConfig extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udprod/system/form_field/fieldsets_config.phtml');
        }
    }

    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_getElementHtml($element);
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        return $html;
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }

    public function getEditFieldsConfigSelect2Json()
    {
        return Mage::helper('udprod')->getEditFieldsConfigSelect2Json();
    }

    public function getFieldContainerBlock($fieldName)
    {
        return Mage::app()->getLayout()->getBlockSingleton('udprod/adminhtml_systemConfigField_fieldsetsColumnConfig')
            ->setTemplate('udprod/system/form_field/fieldset_column_config.phtml')
            ->setFieldName($fieldName);
    }

}