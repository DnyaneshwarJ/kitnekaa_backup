<?php

class Unirgy_DropshipTierShipping_Block_Vendor_V2_SimpleRates extends  Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('unirgy/tiership/vendor/v2/simple_rates.phtml');
        }
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        if (!$element->getDeliveryType()) {
            $html = '<div id="'.$element->getHtmlId().'_container"></div>';
        } else {
            $html = $this->toHtml();
        }
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

}