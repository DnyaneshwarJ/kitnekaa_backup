<?php

class Unirgy_DropshipVendorPromotions_Block_Vendor_Rules extends Mage_Core_Block_Template
{
    protected $_collection;
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
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        if ($toolbar = $this->getLayout()->getBlock('udpromo.grid.toolbar')) {
            $toolbar->setCollection($this->getRulesCollection());
        }

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
        return parent::getUrl($route, $params);
    }

    protected function _afterToHtml($html)
    {
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }

    protected function _applyRequestFilters($collection)
    {
        $r = Mage::app()->getRequest();
        $param = $r->getParam('filter_rule_name');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('name', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_coupon_code');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('code', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_rule_status');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('is_active', $param);
        }
        if (($v = $r->getParam('filter_rule_date_from'))) {
            $collection->addFieldToFilter('from_date', array('gteq'=>Mage::helper('udropship')->dateLocaleToInternal($v, null, true)));
        }
        if (($v = $r->getParam('filter_rule_date_to'))) {
            $_filterDate = Mage::app()->getLocale()->date();
            $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            $_filterDate->addDay(1);
            $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
            $collection->addFieldToFilter('from_date', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
        }
        if (($v = $r->getParam('filter_rule_expire_from'))) {
            $collection->addFieldToFilter('to_date', array('gteq'=>Mage::helper('udropship')->dateLocaleToInternal($v, null, true)));
        }
        if (($v = $r->getParam('filter_rule_expire_to'))) {
            $_filterDate = Mage::app()->getLocale()->date();
            $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            $_filterDate->addDay(1);
            $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
            $collection->addFieldToFilter('to_date', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
        }
        $collection->addFieldToFilter('udropship_vendor', $this->getVendor()->getId());
        return $this;
    }

    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getRulesCollection()
    {
        if (!$this->_collection) {
            $v = Mage::getSingleton('udropship/session')->getVendor();
            if (!$v || !$v->getId()) {
                return array();
            }
            $r = Mage::app()->getRequest();
            $res = Mage::getSingleton('core/resource');
            $collection = Mage::getModel('salesrule/rule')->getCollection();

            $this->_applyRequestFilters($collection);

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

}