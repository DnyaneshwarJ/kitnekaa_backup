<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_Vendor_Helper_Renderer_CategoriesCheckboxes extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _prepareLayout()
    {
        $this->setTemplate('udropship/vendor/helper/categories_checkboxes_tree.phtml');
    }

    protected $_oldStoreId;
    protected $_unregUrlStore;
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!Mage::registry('url_store')) {
            $this->_unregUrlStore = true;
            Mage::register('url_store', Mage::app()->getStore());
        }
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        return $this;
    }
    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = array())
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        if (Mage::registry('url_store')) {
            $params['_store'] = Mage::registry('url_store')->getId();
        } else {
            $params['_store'] = Mage::app()->getDefaultStoreView()->getId();
        }
        return parent::getUrl($route, $params);
    }
    protected function _afterToHtml($html)
    {
        Mage::helper('udropship/catalog')->setDesignStore();
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }

    protected $_element = null;

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

    public function getLoadTreeUrl($expanded=null)
    {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if (
            (is_null($expanded) && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('udprod/adminhtml_widget/categoriesJson', $params);
    }

}