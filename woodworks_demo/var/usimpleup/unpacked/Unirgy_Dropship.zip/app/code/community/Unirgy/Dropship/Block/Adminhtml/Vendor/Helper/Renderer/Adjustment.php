<?php

class Unirgy_Dropship_Block_Adminhtml_Vendor_Helper_Renderer_Adjustment extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('udropship/vendor/statement/adjustment.phtml');
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
                    'label' => Mage::helper('udropship')->__('Add Adjustment'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button'
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
    
    public function getPoTypeSelect($name, $id=null)
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setClass('required-entry validate-state')
            ->setValue($this->getStatement()->getPoType())
            ->setOptions(Mage::getSingleton('udropship/source')->setPath('statement_po_type')->toOptionHash());

        $select->setName($name);
        if (!is_null($id)) $select->setId($id);
            
        return $select->getHtml();
    }

}