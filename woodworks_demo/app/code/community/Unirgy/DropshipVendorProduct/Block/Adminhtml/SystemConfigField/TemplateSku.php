<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_SystemConfigField_TemplateSku extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udprod/system/form_field/template_sku_config.phtml');
        }
        if (($head = Mage::app()->getLayout()->getBlock('head'))) {
            $head->setCanLoadExtJs(true);
        }
    }

    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_getElementHtml($element);
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        if (!$this->getTypeOfProduct()) {
            $html = '<div id="'.$element->getHtmlId().'_container"></div>';
        } else {
            $html = $this->_toHtml();
        }
        return $html;
    }

    public function getConfigurableAttributes($setId)
    {
        static $prod;
        if (null === $prod) {
            $prod = Mage::getModel('udprod/product')->setTypeId('configurable');
        }
        list($_setId) = explode('-', $setId);
        $prod->setAttributeSetId($_setId);
        $_cfgAttributes = array();
        $cfgAttributes = $prod->getTypeInstance(true)
            ->getSetAttributes($prod);
        foreach ($cfgAttributes as $cfgAttribute) {
            if ($prod->getTypeInstance(true)->canUseAttribute($cfgAttribute, $prod)) {
                $_cfgAttributes[$cfgAttribute->getId()] = $cfgAttribute->getFrontend()->getLabel();
            }
        }
        return $_cfgAttributes;
    }

    public function getSetIds()
    {
        $_setIds = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionHash();
        $setIds = array();
        $_options = Mage::getStoreConfig('udprod/general/type_of_product');
        if (!is_array($_options)) {
            $_options = unserialize($_options);
        }
        $options = array();
        if (is_array($_options)) {
            foreach ($_options as $opt) {
                if ($this->getTypeOfProduct() == $opt['type_of_product']) {
                    $options = $opt['attribute_set'];
                    break;
                }
            }
        }
        foreach ($_setIds as $_setId => $_setIdLbl) {
            if (in_array($_setId, $options)) {
                $setIds[$_setId] = $_setIdLbl;
            }
        }
        return $setIds;
    }

    public function getCfgValue($cfg, $key, $subKey)
    {
        if (false === strpos($key, '-')) {
            $key = $key.'-'.$this->getTypeOfProduct();
        }
        list($_key) = explode('-', $key);
        $result = @$cfg[$key][$subKey];
        if (!isset($cfg[$key])) {
            $result = @$cfg[$_key][$subKey];
        }
        return $result;
    }

}