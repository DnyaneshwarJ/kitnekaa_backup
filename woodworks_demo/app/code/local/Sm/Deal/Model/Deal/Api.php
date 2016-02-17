<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Deal_Api extends Mage_Api_Model_Resource_Abstract{

	protected function _initDeal($dealId){
		$deal = Mage::getModel('deal/deal')->load($dealId);
		if (!$deal->getId()) {
			$this->_fault('deal_not_exists');
		}
		return $deal;
	}

	public function items($filters = null){
		$collection = Mage::getModel('deal/deal')->getCollection();
		$apiHelper = Mage::helper('api');
		$filters = $apiHelper->parseFilters($filters);
		try {
			foreach ($filters as $field => $value) {
				$collection->addFieldToFilter($field, $value);
			}
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('filters_invalid', $e->getMessage());
		}
		$result = array();
		foreach ($collection as $deal) {
			$result[] = $deal->getData();
		}
		return $result;
	}

	public function add($data){
		try {
			if (is_null($data)){
				throw new Exception(Mage::helper('deal')->__("Data cannot be null"));
			}
			$deal = Mage::getModel('deal/deal')
				->setData((array)$data)
				->save();
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		} 
		catch (Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return $deal->getId();
	}

	public function update($dealId, $data){
		$deal = $this->_initDeal($dealId);
		try {
			$deal->addData((array)$data);
			$deal->save();
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('save_error', $e->getMessage());
		}
		
		return true;
	}

	public function remove($dealId){
		$deal = $this->_initDeal($dealId);
		try {
			$deal->delete();
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('remove_error', $e->getMessage());
		}
		return true;
	}

	public function info($dealId){
		$result = array();
		$deal = $this->_initDeal($dealId);
		$result = $deal->getData();
		//related products
		$result['products'] = array();
		$relatedProductsCollection = $deal->getSelectedProductsCollection();
		foreach ($relatedProductsCollection as $product) {
			$result['products'][$product->getId()] = $product->getPosition();
		}
		return $result;
	}

	public function assignProduct($dealId, $productId, $position = null){
		$deal = $this->_initDeal($dealId);
		$positions	= array();
		$products 	= $deal->getSelectedProducts();
		foreach ($products as $product){
			$positions[$product->getId()] = array('position'=>$product->getPosition());
		}
		$product = Mage::getModel('catalog/product')->load($productId);
		if (!$product->getId()){
			$this->_fault('product_not_exists'); 
		}
		$positions[$productId]['position'] = $position;
		$deal->setProductsData($positions);
		try {
			$deal->save();
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return true;
	}

	public function unassignProduct($dealId, $productId){
		$deal = $this->_initDeal($dealId);
		$positions	= array();
		$products 	= $deal->getSelectedProducts();
		foreach ($products as $product){
			$positions[$product->getId()] = array('position'=>$product->getPosition());
		}
		unset($positions[$productId]);
		$deal->setProductsData($positions);
		try {
			$deal->save();
		} 
		catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}
		return true;
	}
}