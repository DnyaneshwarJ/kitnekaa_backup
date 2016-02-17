<?php

class Unirgy_Dropship_Block_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('udropship/categories.phtml');
        Mage::helper('udropship')->disableJrdEmptyCatEvent();
    }
    protected function _prepareLayout()
    {
        return Mage_Core_Block_Abstract::_prepareLayout();
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
    public function getLoadTreeUrl($expanded=null)
    {
        if ($this->hasForcedIdsString()) {
            $idName = $this->getIdName() ? $this->getIdName() : 'product_categories';
            $nameName = $this->getNameName() ? $this->getNameName() : 'category_ids';
            $idsString = $this->getForcedIdsString();
            $store = Mage::app()->getDefaultStoreView();
            $oldStoreSecure = $store->getConfig('web/secure/use_in_frontend');
            $store->setConfig('web/secure/use_in_frontend', Mage::app()->getStore()->isCurrentlySecure());
            $url = $this->getUrl('udropship/index/categoriesJson', array(
                '_current'=>true,
                'name_name'=>$nameName,
                'id_name'=>$idName,
                '_secure'=>Mage::app()->getStore()->isCurrentlySecure(),
                'ids_string'=>$idsString,
            ));
            $store->setConfig('web/secure/use_in_frontend', $oldStoreSecure);
            return $url;
        } elseif (Mage::helper('udropship')->isModuleActive('udprod')) {
            return $this->getUrl('udprod/vendor/categoriesJson', array('_current'=>true));
        }
    }
    public function getCategoryIds()
    {
        return $this->hasForcedIdsString()
            ? explode(',', $this->getForcedIdsString())
            : parent::getCategoryIds();
    }
    public function isReadonly()
    {
        return $this->hasForcedIdsString() ? false : parent::isReadonly();
    }
    public function render()
    {
        return $this->toHtml();
    }

    public function getCategoryChildrenJson($categoryId)
    {
        if (!Mage::getSingleton('udropship/session')->isLoggedIn()) {
            return parent::getCategoryChildrenJson($categoryId);
        }
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);

        if (!$node || !$node->hasChildren()) {
            return '[]';
        }

        $children = array();
        foreach ($node->getChildren() as $child) {
            if (!$child->getIsActive() && !Mage::getStoreConfigFlag('udprod/general/show_hidden_categories')) continue;
            if (!$this->isVendorEnabled($child->getId())) continue;
            $children[] = $this->_getNodeJson($child);
        }

        return Mage::helper('core')->jsonEncode($children);
    }

    public function isVendorEnabled($cId=null)
    {
        $flag = !($v = Mage::getSingleton('udropship/session')->getVendor()) || !$v->getIsLimitCategories();
        if (!$flag && !is_null($cId)) {
            if ($v->getIsLimitCategories() == 1) {
                $flag = in_array($cId, $this->getVendorCategoryIds());
            } elseif ($v->getIsLimitCategories() == 2) {
                $flag = !in_array($cId, $this->getVendorCategoryIds());
            }
        }
        return $flag;
    }
    protected $_vendorCatIds;
    public function getVendorCategoryIds()
    {
        if (is_null($this->_vendorCatIds)) {
            $this->_vendorCatIds = array();
            if (($v = Mage::getSingleton('udropship/session')->getVendor()) && $v->getIsLimitCategories()) {
                $this->_vendorCatIds = explode(',', implode(',', (array)$v->getLimitCategories()));
            }
        }
        return $this->_vendorCatIds;
    }
    public function getRoot($parentNodeCategory=null, $recursionLevel=3)
    {
        $root = parent::getRoot($parentNodeCategory, $recursionLevel);
        if (!$this->isVendorEnabled($root->getId())) {
            $root->setDisabled(true);
        }
        return $root;
    }
    protected function _getNodeJson($node, $level=1)
    {
        $item = parent::_getNodeJson($node, $level);
        if (!$this->isVendorEnabled($item['id'])) {
            $item['disabled'] = true;
        }
        return $item;
    }
}