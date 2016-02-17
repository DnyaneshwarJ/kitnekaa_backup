<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_SystemConfigField_TypeOfProduct extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udprod/system/form_field/type_of_product.phtml');
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
                    'id'    => 'udprodTypeOfProduct_config_add_new_option_button'
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

    public function getAttributeSetSelect($name, $value=null, $id=null)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setClass('required-entry validate-state')
            ->setValue($value)
            ->setExtraParams('multiple="multiple" style="height: 300px"')
            ->setOptions($this->getSetIds());

        $select->setName($name);
        if (!is_null($id)) $select->setId($id);

        return $select->getHtml();
    }

    public function getSetIds()
    {
        return Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionHash();
    }

}