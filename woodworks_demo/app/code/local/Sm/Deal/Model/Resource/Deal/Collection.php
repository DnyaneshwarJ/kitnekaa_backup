<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Resource_Deal_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	protected $_joinedFields = array();

	public function _construct(){
		parent::_construct();
		$this->_init('deal/deal');
		$this->_map['fields']['store'] = 'store_table.store_id';
	}

	protected function _toOptionArray($valueField='entity_id', $labelField='name', $additional=array()){
		return parent::_toOptionArray($valueField, $labelField, $additional);
	}

	protected function _toOptionHash($valueField='entity_id', $labelField='name'){
		return parent::_toOptionHash($valueField, $labelField);
	}

	public function addStoreFilter($store, $withAdmin = true){
		if (!isset($this->_joinedFields['store'])){
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
			if (!is_array($store)) {
				$store = array($store);
			}
			if ($withAdmin) {
				$store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
			}
			$this->addFilter('store', array('in' => $store), 'public');
			$this->_joinedFields['store'] = true;
		}
		return $this;
	}

	protected function _renderFiltersBefore(){
		if ($this->getFilter('store')) {
			$this->getSelect()->join(
				array('store_table' => $this->getTable('deal/deal_store')),
				'main_table.entity_id = store_table.deal_id',
				array()
			)->group('main_table.entity_id');
			$this->_useAnalyticFunction = true;
		}
		return parent::_renderFiltersBefore();
	}

	public function getSelectCountSql(){
		$countSelect = parent::getSelectCountSql();
		$countSelect->reset(Zend_Db_Select::GROUP);
		return $countSelect;
	}

	public function addProductFilter($product){
		if ($product instanceof Mage_Catalog_Model_Product){
			$product = $product->getId();
		}
		if (!isset($this->_joinedFields['product'])){
			$this->getSelect()->join(
				array('related_product' => $this->getTable('deal/deal_product')),
				'related_product.deal_id = main_table.entity_id',
				array('position')
			);
			$this->getSelect()->where('related_product.product_id = ?', $product);
			$this->_joinedFields['product'] = true;
		}
		return $this;
	}
}