<?php

class Kithnekaa_Solr_Model_Index extends Mage_Core_Model_App
{
	protected $_suggestData = null;
	
	public function getSuggestDataShop($query){
		$kithnekaa_engine = Mage::getSingleton('kithnekaa_solr/engine');
		$storeId = Mage::app()->getStore()->getId();
		$data = array();
		if ( !$kithnekaa_engine->isWorking() ) {
			$data = $kithnekaa_engine->getAutoSuggestionsShoppingList($storeId,$query);
		} else {
			$data = $this->getCollectionDataForShoppingList($storeId,$query);
		}
		
		return $data;
	}
	
	public function getCollectionDataForShoppingList($storeId, $query){

		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('id')
		->addAttributeToSelect('name')
		->addFieldToFilter('name', array( "like" => '%'.$query.'%' ))
		->addFieldToFilter('status', 1)
		->setPageSize(1000) // limit number of results returned
		->setCurPage(1); // set the offset (useful for pagination)
		
		$data = array();
		foreach($collection as $product){
			$data[] = array('product_id' => $product->getId(),
							'product_name' => $product->getName()
			);
		}
		
		return $data;
	}
	
	public function getSuggestData($query){
		
		$counter = 0;
		$data = array();
		$storeId = Mage::app()->getStore()->getId();
		$engine = Mage::getSingleton('kithnekaa_solr/engine');
		
		$facet = $engine->getAutoSuggestionsNestIncubate($storeId, $query);
		
		$product_ids = array();
		
		foreach ( $facet['results'] as $value) {
			$_data = array(
					'title' => $value['product_name'],
					'row_class' => ( ++$counter ) % 2 ? 'odd' : 'even',
					'product' => $value['product_name'],
					'product_id' => $value['product_id'],
					'category' => $this->processCategory($value['product_category_name'])
			);
			$data['results'][] = $_data;
			$product_ids[] = $value['product_id'];
		}
		
		
		$data['num_of_results'] = $facet['totalNumResults'];
		$data['product_collection'] = $this->getProductDetailsFromCollection($product_ids);

		return $data;
	}
	
	public function processCategory($categories){
		return implode(" >> ",$categories);
	}
	
	public function getProductDetailsFromCollection($product_ids){
		$productCollectionResults = array();
		
		$productCollection = Mage::getResourceModel('catalog/product_collection')
		->addAttributeToSelect('product_url')
		->addAttributeToFilter('entity_id', array('in' => $product_ids));
			
		foreach($productCollection as $product){
			$productCollectionResults[$product->getId()] = $product->getProductUrl();
		}
		
		return $productCollectionResults;
	}
}