<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Resource_Deal extends Mage_Core_Model_Resource_Db_Abstract{

	public function _construct(){
		$this->_init('deal/deal', 'entity_id');
	}

	public function lookupStoreIds($dealId){
		$adapter = $this->_getReadAdapter();
		$select  = $adapter->select()
			->from($this->getTable('deal/deal_store'), 'store_id')
			->where('deal_id = ?',(int)$dealId);
		return $adapter->fetchCol($select);
	}

	protected function _afterLoad(Mage_Core_Model_Abstract $object){
		if ($object->getId()) {
			$stores = $this->lookupStoreIds($object->getId());
			$object->setData('store_id', $stores);
		}
		return parent::_afterLoad($object);
	}

	protected function _getLoadSelect($field, $value, $object){
		$select = parent::_getLoadSelect($field, $value, $object);
		if ($object->getStoreId()) {
			$storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
			$select->join(
				array('deal_deal_store' => $this->getTable('deal/deal_store')),
				$this->getMainTable() . '.entity_id = deal_deal_store.deal_id',
				array()
			)
			->where('deal_deal_store.store_id IN (?)', $storeIds)
			->order('deal_deal_store.store_id DESC')
			->limit(1);
		}
		return $select;
	}

	protected function _afterSave(Mage_Core_Model_Abstract $object){
		$oldStores = $this->lookupStoreIds($object->getId());
		$newStores = (array)$object->getStores();
		if (empty($newStores)) {
			$newStores = (array)$object->getStoreId();
		}
		$table  = $this->getTable('deal/deal_store');
		$insert = array_diff($newStores, $oldStores);
		$delete = array_diff($oldStores, $newStores);
		if ($delete) {
			$where = array(
				'deal_id = ?' => (int) $object->getId(),
				'store_id IN (?)' => $delete
			);
			$this->_getWriteAdapter()->delete($table, $where);
		}
		if ($insert) {
			$data = array();
			foreach ($insert as $storeId) {
				$data[] = array(
					'deal_id'  => (int) $object->getId(),
					'store_id' => (int) $storeId
				);
			}
			$this->_getWriteAdapter()->insertMultiple($table, $data);
		}
		return parent::_afterSave($object);
	}

	public function checkUrlKey($urlKey, $storeId, $active = true){
		$stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
		$select = $this->_initCheckUrlKeySelect($urlKey, $stores);
		if (!is_null($active)) {
			$select->where('e.status = ?', $active);
		}
		$select->reset(Zend_Db_Select::COLUMNS)
			->columns('e.entity_id')
			->limit(1);
		
		return $this->_getReadAdapter()->fetchOne($select);
	}

	protected function _initCheckUrlKeySelect($urlKey, $store){
		$select = $this->_getReadAdapter()->select()
			->from(array('e' => $this->getMainTable()))
			->join(
				array('es' => $this->getTable('deal/deal_store')),
				'e.entity_id = es.deal_id',
				array())
			->where('e.url_key = ?', $urlKey)
			->where('es.store_id IN (?)', $store);
		return $select;
	}

	public function getIsUniqueUrlKey(Mage_Core_Model_Abstract $object){
		if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
			$stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
		} 
		else {
			$stores = (array)$object->getData('stores');
		}
		$select = $this->_initCheckUrlKeySelect($object->getData('url_key'), $stores);
		if ($object->getId()) {
			$select->where('e.entity_id <> ?', $object->getId());
		}
		if ($this->_getWriteAdapter()->fetchRow($select)) {
			return false;
		}
		return true;
	}

	protected function isNumericUrlKey(Mage_Core_Model_Abstract $object){
		return preg_match('/^[0-9]+$/', $object->getData('url_key'));
	}

	protected function isValidUrlKey(Mage_Core_Model_Abstract $object){
		return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('url_key'));
	}

	protected function _beforeSave(Mage_Core_Model_Abstract $object){
		if (!$this->getIsUniqueUrlKey($object)) {
			Mage::throwException(Mage::helper('deal')->__('URL key already exists.'));
		}
		if (!$this->isValidUrlKey($object)) {
			Mage::throwException(Mage::helper('deal')->__('The URL key contains capital letters or disallowed symbols.'));
		}
		if ($this->isNumericUrlKey($object)) {
			Mage::throwException(Mage::helper('deal')->__('The URL key cannot consist only of numbers.'));
		}
		return parent::_beforeSave($object);
	}}