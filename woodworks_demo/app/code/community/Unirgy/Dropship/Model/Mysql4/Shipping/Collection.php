<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Mysql4_Shipping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/shipping');
        parent::_construct();
    }

    protected function _afterLoad()
    {
        $items = $this->getColumnValues('shipping_id');
        if (!count($items)) {
            parent::_afterLoad();
            return;
        }

        $table = $this->getTable('udropship/shipping_website');
        $select = $this->getConnection()->select()->from($table)->where($table.'.shipping_id IN (?)', $items);
        if ($result = $this->getConnection()->fetchAll($select)) {
            foreach ($result as $row) {
                $item = $this->getItemById($row['shipping_id']);
                if (!$item) continue;
                $websites = $item->getWebsiteIds();
                if (!$websites) $websites = array();
                $websites[] = $row['website_id'];
                $item->setWebsiteIds($websites);
            }
        }

        $table = $this->getTable('udropship/shipping_method');
        $select = $this->getConnection()->select()->from($table)->where($table.'.shipping_id IN (?)', $items);
        $tblColumns = $this->getConnection()->describeTable($table);
        if (isset($tblColumns['profile'])) {
            $select->order(new Zend_Db_Expr("{$table}.profile='default'"));
        }
        if (isset($tblColumns['sort_order'])) {
            $select->order(new Zend_Db_Expr("{$table}.sort_order"));
        }
        if ($result = $this->getConnection()->fetchAll($select)) {
            foreach ($result as $row) {
                $item = $this->getItemById($row['shipping_id']);
                if (!$item) continue;
                $methods = $item->getSystemMethodsByProfile();
                $fullMethods = $item->getFullSystemMethodsByProfile();
                if (!$methods) $methods = array();
                $profile = 'default';
                if (!empty($row['profile'])
                    && Mage::helper('udropship')->isUdsprofileActive()
                    && Mage::helper('udsprofile')->hasProfile($row['profile'])
                ) {
                    $profile=$row['profile'];
                }
                $methods[$profile][$row['carrier_code']][$row['method_code']] = $row['method_code'];
                $item->setSystemMethodsByProfile($methods);
                $fullMethods[$profile][] = $row;
                $item->setFullSystemMethodsByProfile($fullMethods);
            }
            foreach ($result as $row) {
                $item = $this->getItemById($row['shipping_id']);
                if (!$item || empty($row['est_use_custom'])) continue;
                $methods = $item->getSystemMethodsByProfile();
                $fullMethods = $item->getFullSystemMethodsByProfile();
                if (!$methods) $methods = array();
                $profile = 'default';
                if (!empty($row['profile'])
                    && Mage::helper('udropship')->isUdsprofileActive()
                    && Mage::helper('udsprofile')->hasProfile($row['profile'])
                ) {
                    $profile=$row['profile'];
                }
                $methods[$profile][$row['est_carrier_code']][$row['est_method_code']] = $row['est_method_code'];
                $item->setSystemMethodsByProfile($methods);
                $fullMethods[$profile][] = $row;
                $item->setFullSystemMethodsByProfile($fullMethods);
            }
            $item->setSystemMethods(@$methods['default']);
        }

        parent::_afterLoad();
    }

    public function addWebsiteFilter($website)
    {
        if ($website instanceof Mage_Core_Model_Website) {
            $website = array($website->getId());
        }

        if ($this->getFlag('website_filter')) return $this;

        $this->getSelect()->join(
            array('website_table' => $this->getTable('udropship/shipping_website')),
            'main_table.shipping_id = website_table.shipping_id',
            array()
        )->where('website_table.website_id in (?)', array(0, $website));

        $this->setFlag('website_filter', 1);

        return $this;
    }

    public function joinVendor($vendorIds, $method='joinLeft')
    {
        if (!is_array($vendorIds)) {
            $vendorIds = array($vendorIds);
        }

        if ($this->getFlag('join_vendor')) return $this;

        $vendorFilter = $this->getConnection()->quoteInto('vendor_table.vendor_id in (?)', $vendorIds);
        $this->getSelect()->$method(
            array('vendor_table' => $this->getTable('udropship/vendor_shipping')),
            'main_table.shipping_id = vendor_table.shipping_id and '.$vendorFilter,
            array('vendor_shipping_id', 'account_id', 'price_type', 'price', 'priority', 'handling_fee', 'carrier_code', 'est_carrier_code','allow_extra_charge','extra_charge_suffix','extra_charge_type','extra_charge')
        );

        $this->setFlag('join_vendor', 1);

        return $this;
    }
}