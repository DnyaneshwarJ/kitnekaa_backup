<?php

class Kithnekaa_Solr_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction(){
	
		$engine = Mage::getSingleton('kithnekaa_solr/engine');
		
		$solrResults = $engine->getAutoSuggestionsNestIncubate($storeId, $queryString);
		
		$category = array();
		$product_details = array();
		
			foreach($solrResults as $result){
			
				$productCollection = Mage::getResourceModel('catalog/product_collection')
							->addAttributeToSelect('*')
							->addAttributeToFilter('entity_id', array('in' => $result['product_id']));
			
				foreach($productCollection as $product){
					$productName = $product->getName();

					$categories = $product->getCategoryCollection()->load(10)->addNameToResult();var_dump($categories);exit;
					//->addAttributeToSelect('name');
					foreach($categories as $c){
						echo $c->getName();exit;
					}
				}
				
				
				var_dump($categories);
				
				if($result['product_category_id']){
				
					//category names
					foreach($result['product_category_id'] as $category_id){
						$categoryName = Mage::getModel('catalog/category')->load($category_id)->getName();
						
						array_push($category, $categoryName);	
					}
				}
			
				$product_details['product_name'] = $product->getName();
				$product_details['product_category'] = $product->getCategory();
			}
		//print_r($product_details);exit;
		//->addAttributeToFilter('entity_id', array('in' => $productIds))
		//->addFieldToFilter('status', 1);
		//->addFieldToFilter('product_d', 197);
		var_dump($product);exit;
		echo $product->getName();
		echo "<br>";
		echo $product->getProductUrl();exit;
			
			$categoryIds = array(10, 3);
			
			foreach($categoryIds as $catIds){
				$category = Mage::getModel('catalog/category')->load($category_id);
				
				$current_category = $category;
				$product_category = $category->getName();
			}
	}
	
	public function indeAction(){
		
	$collection= Mage::getModel('catalog/category')->getCollection() 
	->addAttributeToSelect('name') 
->addAttributeToSelect('is_active');
$names = array();
foreach($collection as $col) 
{

$names[] = $col->getName();

}
		var_dump($names);	
	}

	public function bAction(){
		$categoriesArray = Mage::getModel('catalog/category')
		->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSort('path', 'asc')
		->addFieldToFilter('is_active', array('eq'=>'1'))
		->load()
		->toArray();
		
		
		foreach ($categoriesArray as $categoryId => $category) {
			if (isset($category['name'])) {
				$categories[] = array(
						'label' => $category['name'],
						'level'  =>$category['level'],
						'value' => $categoryId
				);
			}
		}
		//return $categories;
		var_dump($categories);
	}
	
	public function shopAction($query){
		$query = "abras";
			$kithnekaa_model = Mage::getSingleton('kithnekaa_solr/index');
			
		//	$query = $this->helper('catalogsearch')->getQueryText();
			$data = $kithnekaa_model->getSuggestDataShop($query);
		echo "<pre>";
		print_r($data);
		
	}
}