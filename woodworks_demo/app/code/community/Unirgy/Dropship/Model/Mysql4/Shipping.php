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

class Unirgy_Dropship_Model_Mysql4_Shipping extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/shipping', 'shipping_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);

        $id = $object->getId();
        if (!$id) {
            return;
        }

        $read = $this->_getReadAdapter();

        $table = $this->getTable('udropship/shipping_website');
        $select = $read->select()->from($table)->where($table.'.shipping_id=?', $id);
        if ($result = $read->fetchAll($select)) {
            foreach ($result as $row) {
                $websites = $object->getWebsiteIds();
                if (!$websites) $websites = array();
                $websites[] = $row['website_id'];
                $object->setWebsiteIds($websites);
            }
        }

        $table = $this->getTable('udropship/shipping_method');
        $select = $read->select()->from($table)->where($table.'.shipping_id=?', $id);
        $tblColumns = $this->_getReadAdapter()->describeTable($table);
        if (isset($tblColumns['profile'])) {
            $select->order(new Zend_Db_Expr("{$table}.profile='default'"));
        }
        if (isset($tblColumns['sort_order'])) {
            $select->order(new Zend_Db_Expr("{$table}.sort_order"));
        }
        if ($result = $read->fetchAll($select)) {
            foreach ($result as $row) {
                $methods = $object->getSystemMethodsByProfile();
                $fullMethods = $object->getFullSystemMethodsByProfile();
                if (!$methods) $methods = array();
                $profile = 'default';
                if (!empty($row['profile'])
                    && Mage::helper('udropship')->isUdsprofileActive()
                    && Mage::helper('udsprofile')->hasProfile($row['profile'])
                ) {
                    $profile=$row['profile'];
                }
                $methods[$profile][$row['carrier_code']][$row['method_code']] = $row['method_code'];
                $object->setSystemMethodsByProfile($methods);
                $fullMethods[$profile][] = $row;
                $object->setFullSystemMethodsByProfile($fullMethods);
            }
            if (Mage::helper('udropship')->isUdsprofileActive()) {
                foreach ($result as $row) {
                    $methods = $object->getSystemMethodsByProfile();
                    if (!$methods || empty($row['est_use_custom'])) $methods = array();
                    $profile = 'default';
                    if (!empty($row['profile'])
                        && Mage::helper('udropship')->isUdsprofileActive()
                        && Mage::helper('udsprofile')->hasProfile($row['profile'])
                    ) {
                        $profile=$row['profile'];
                    }
                    $methods[$profile][$row['est_carrier_code']][$row['est_method_code']] = $row['est_method_code'];
                    $object->setSystemMethodsByProfile($methods);
                }
            }
            $object->setSystemMethods(@$methods['default']);
        }
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        parent::_afterSave($object);

        $write = $this->_getWriteAdapter();

        $table = $this->getTable('udropship/shipping_website');
        $write->delete($table, $write->quoteInto('shipping_id=?', $object->getId()));
        $websiteIds = $object->getWebsiteIds();
        if (in_array(0, $websiteIds)) {
            $websiteIds = array(0);
        }
        foreach ($websiteIds as $wId) {
            $write->insert($table, array('shipping_id'=>$object->getId(), 'website_id'=>(int)$wId));
        }

        $table = $this->getTable('udropship/shipping_method');
        if ($object->getPostedSystemMethods()) {
            $write->delete($table, $write->quoteInto('shipping_id=?', $object->getId()));
            foreach ($object->getPostedSystemMethods() as $c=>$m) {
                if (!$m) continue;
                $write->insert($table, array('shipping_id'=>$object->getId(), 'carrier_code'=>$c, 'method_code'=>$m));
            }
        }

        if ($object->hasStoreTitles()) {
            $this->saveStoreTitles($object->getId(), $object->getStoreTitles());
        }
    }

    public function saveStoreTitles($shippingId, $titles)
    {
        $deleteByStoreIds = array();
        $table   = $this->getTable('udropship/shipping_title');
        $adapter = $this->_getWriteAdapter();

        $data    = array();
        foreach ($titles as $storeId => $title) {
            if (Mage::helper('core/string')->strlen($title)) {
                $data[] = array('shipping_id' => $shippingId, 'store_id' => $storeId, 'title' => $title);
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $adapter->beginTransaction();
        try {
            if (!empty($data)) {
                $adapter->insertOnDuplicate(
                    $table,
                    $data,
                    array('title')
                );
            }

            if (!empty($deleteByStoreIds)) {
                $adapter->delete($table, array(
                    'shipping_id=?'       => $shippingId,
                    'store_id IN (?)' => $deleteByStoreIds
                ));
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;

        }
        $adapter->commit();

        return $this;
    }

    public function getStoreTitles($shippingId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('udropship/shipping_title'), array('store_id', 'title'))
            ->where('shipping_id = :shipping_id');
        return $this->_getReadAdapter()->fetchPairs($select, array(':shipping_id' => $shippingId));
    }

    public function getStoreTitle($shippingId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('udropship/shipping_title'), 'title')
            ->where('shipping_id = :shipping_id')
            ->where('store_id IN(0, :store_id)')
            ->order('store_id DESC');
        return $this->_getReadAdapter()->fetchOne($select, array(':shipping_id' => $shippingId, ':store_id' => $storeId));
    }

}