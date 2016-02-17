<?php
/*------------------------------------------------------------------------
 # SM Tab Listing - Version 1.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Tablisting_Block_List extends Mage_Catalog_Block_Product_Abstract
{
	protected $_config = null;
	protected $products_viewed = null;
	
	public $is_ajax = '';

	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('tablisting/data')->get($attributes);
	}

	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('tablisting/data')->get(null);
		}
		if (!is_null($name) && !empty($name)){
			$valueRet = isset($this->_config[$name]) ? $this->_config[$name] : $value;
			return $valueRet;
		}
		return $this->_config;
	}
	
	public function setConfig($name=null, $value=null){
		if (is_null($this->_config)) $this->getConfig();
		if (is_array($name)){
			//Mage::log($name);
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name)){
			$this->_config[$name] = $value;
		}
		return true;
	}
	
	public function getConfigObject(){
        return (object)$this->getConfig();
	}
	
	
	public function generateHash(){
		$config = $this->getConfig();
		$this->hash = md5( serialize($config) );
		return $this->hash;
	}
	
	public function _beforeHtml(){
		$this->generateHash();
	}

	protected function _toHtml(){
		if(!$this->getConfig('isenabled')) return;
		$is_ajax = Mage::app()->getRequest()->getParam('is_ajax');
		if( $is_ajax ){
			$cat_id    = Mage::app()->getRequest()->getParam('tab_cat_id');
			$order_id  = Mage::app()->getRequest()->getParam('order_id');
			$type      = Mage::app()->getRequest()->getParam('data_type');
			if( $type == 'order' ){
				$child_items = $this->getProductsOrder($order_id);
			} else {
				$child_items = $this->getProducts($cat_id);
			}
			$template_file = 'sm/tablisting/default_items.phtml';
		}else{
			$template_file = 'sm/tablisting/default.phtml';
		}
		$this->setTemplate($template_file);
		return parent::_toHtml();
	}
		
	public function getStoreId(){
		if (is_null($this->_storeId)){
			$this->_storeId = Mage::app()->getStore()->getId();
		}
		return $this->_storeId;
	}
	public function setStoreId($storeId=null){
		$this->_storeId = $storeId;
	}	
	
	protected function getProductCollection(){
		$collection = Mage::getSingleton('catalog/product')->getCollection();
		$collection->addAttributeToSelect('*');
		$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
		$visibility = array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
		);
		$collection->addAttributeToFilter('visibility', $visibility);
		// add price data
		$collection->addPriceData();
		$this->_addViewsCount($collection);
		$this->_addReviewsCount($collection);
		$this->_addOrderedCount($collection);
		return $collection;
	}
	
	public function setProductCollection($collection=null){
		$this->_productCollection = $collection;
	}	
	
	public function getTotal(){
		if ($this->getConfig('product_category')==''){
			return;
		}
		$storeId = Mage::app()->getStore()->getId();
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->setStoreId($storeId);
		$category_collection->addIsActiveFilter();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIdFilter( $this->getConfig('product_category') );
		$list = array();
		$items = array();
		$result = array();
		foreach ($category_collection as $category) {
			$category_obj		= new stdClass();
			$category_obj->id 		= $category->getId();
			// category products
			$product_collection = $category->getProductCollection();
			$product_collection->addAttributeToSelect('*');
			$product_collection->addStoreFilter($storeId);
	
			// select active & visible in Catalog products
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($product_collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($product_collection);
	
			foreach ($product_collection as $product){
				$product_obj = new stdClass();
				$product_obj->id = $product->getId();
				$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
				$stock = $product->getStockItem();
				$status = $stock->getIsInStock();
				if( $status == '1' ){
					$items[$product_obj->id] = $product_obj;
				}
				
			}
			$result = $items;
		}
		return $result;
	}
	public function getProducts($cat_id ){
		$collection = $this->getProductCollection();
		$params = Mage::app()->getRequest()->getParams();
		if( $params != null ){
			$is_ajax = $params['is_ajax'];
			$cat_id = $params['tab_cat_id'];
			$cat_config = $params['config_categoryid'];
		}
		
		$items = array();
		$this->addReviewSummaryTemplate('sm', 'sm/tablisting/summary.phtml');
		$items = array();
		if ( Mage::registry('current_category') ){
			//is category view page.
			$current_category = Mage::registry('current_category');
			$current_category_id = $current_category->getId();
			$product_ids = $current_category->getProductCollection()->getAllIds();
			$collection->addIdFilter($product_ids);
			$category_ids = array();
		} else {
			if( $params != null && $is_ajax ){
				if( $cat_id != '*' ){
					$category_ids = preg_split("/[,\s\D]+/", $cat_id);
				} else {
					$category_ids = preg_split("/[,\s\D]+/", $cat_config);
				}
			} else {
				$category_ids = preg_split("/[,\s\D]+/", $this->_config['product_category']);
			}
			if (is_array($category_ids)){
				foreach ($category_ids as $i => $id) {
					if (!is_numeric($id)){
						unset($category_ids[$i]);
					}
				}
			}
		}
		if (isset($category_ids) && count($category_ids)>0) $this->_addCategoryFilter($collection, $category_ids);
		// Sort products in collection
		$dir = strtolower( $this->_config['product_order_dir'] );
		if (!in_array($dir, array('asc', 'desc'))){
			$dir = 'asc';
		}
	
		$attribute_to_sort = $this->_config['product_order_by'];
		switch ($attribute_to_sort){
			case 'name':
			case 'created_at':
			case 'price':
				$collection->addAttributeToSort($attribute_to_sort, $dir);
				break;
			case 'position':
				break;
			case 'random':
				$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
				break;
			case 'top_rating':
				$collection->getSelect()->order('sm_rating_summary desc');
				break;
			case 'most_reviewed':
				$collection->getSelect()->order('sm_reviews_count desc');
				break;
			case 'most_viewed':
				$collection->getSelect()->order('sm_views_count desc');
				break;
			case 'best_sales':
				$collection->getSelect()->order('sm_ordered_count desc');
				break;
		}
		
		if( $params != null && $is_ajax ){
			$product_limitation = intval(Mage::app()->getRequest()->getParam('ajax_tablisting_start'));
			$collection->getSelect()->limit( $this->_config['product_limitation'], $product_limitation );
		} else {
			$product_limitation = intval($this->_config['product_limitation']);
			if ( $product_limitation > 0 ){
				$collection->setPageSize($product_limitation);
			}
		}
		$maxtitle = $this->getConfig('item_title_max_characs',-1);
		foreach( $collection as $k => $product ) {
			$product_obj = new stdClass();
			$product_obj->id = $product->getId();
	
			if ( $maxtitle  > 0 ){
				$product_obj->title = Mage::helper('tablisting/data')->truncate($product->getName(), $maxtitle, '');
			} else {
				$product_obj->title = $product->getName();
			}
			$description = $product->getShortDescription();
			if ( (int)$this->getConfig('item_description_striptags') == 1 ){
				$keep_tags = $this->getConfig('item_description_keeptags', '');
				$keep_tags = str_replace(array(' '), array(''), $keep_tags);
				$tmp_desc = strip_tags($description ,$keep_tags );
				$product_obj->description = $tmp_desc;
			} else {
				$product_obj->description = $description;
			}
			if (($maxchars=$this->getConfig('item_desc_max_characs',-1))>0){
				$product_obj->description = Mage::helper('tablisting/data')->truncate($product_obj->description, $maxchars, '');
			}
			$product_obj->image = (string)Mage::helper('catalog/image')->init($product, 'image')->resize($this->getConfig('item_image_width'), $this->getConfig('item_image_height'));
			$product_obj->link = $product->getProductUrl();
			$product_obj->price_html = $this->getPriceHtml($product, true);
			$product_obj->review_html = $this->getReviewsSummaryHtml($product, 'sm', true);
			$product_obj->orderid =  $attribute_to_sort;
			$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
			$stock = $product->getStockItem();
			$status = $stock->getIsInStock();
			if( $status == '1' ){
				$items[$product_obj->id] = $product_obj;
			}
			//$items[$product_obj->id] = $product_obj;
		}
		return $items;
	}	
	public function getCategory(){
		$list = array();
		$params = Mage::app()->getRequest()->getParams();
		if( $params != null ){
			$is_ajax = $params['is_ajax'];
			$cat_id = $params['tab_cat_id'];
		}
		$orderid = $this->_config['product_order_by'];		
		$total = count($this->getTotal());
		//var_dump($total);
		$all = new stdClass();
		$all->id   = '*';
		$all->count = $total;
		$all->title = 'All Products';
		$all->sel   = 'sel';
		$all->orderid   = $orderid;
		$all->child = $this->getProducts('*');
		if ( $params != null && $is_ajax ){
			$all->child = $this->getProducts('*');
		}
		array_unshift($list, $all);
	
		if ( $this->getConfig('product_category')=='' ){
			return array();
		}
		
		$storeId = Mage::app()->getStore()->getId();
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->setStoreId($storeId);
		$category_collection->addIsActiveFilter();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIdFilter( $this->getConfig('product_category') );
		
		foreach ($category_collection as $category) {
			$items = array();
			$category_obj		= new stdClass();
			$category_obj->id 	= $category->getId();
			$category_obj->title = $category->getName();
			$category_obj->link		= $category->getUrl();
			$product_collection     = $category->getProductCollection();
			foreach( $product_collection as $product ){
				$product_obj = new stdClass();
				$product_obj->id = $product->getId();
				$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
				$stock = $product->getStockItem();
				$status = $stock->getIsInStock();
				if( $status == '1' ){
					$items[$product_obj->id] = $product_obj;
				}					
			}
			$category_obj->count    = count($items);	
			$category_obj->orderid   = $orderid;
			if ( $params != null && $is_ajax ){
				$category_obj->child    = $this->getProducts($cat_id);
			}
			$list[]= $category_obj;
		}
		return $list;
	}
	

	
	public function getCategoryOrder(){
		$total = count($this->getTotal());
		$order_by = preg_split("/\,/", $this->_config['order_tab']);
		$items = array();
		while( count($order_by) ){
			$item = array();
			$item = trim(array_shift($order_by));
			array_push($items, $item);
		}
		
		$order_attr  = Mage::getModel('tablisting/system_config_source_orderBy');
		$order_label = $order_attr->toOptionArray(true);

		$list = array();
		$params = Mage::app()->getRequest()->getParams();
		if( $params ){
			$is_ajax = $params['is_ajax'];
			$cat_id = $params['tab_cat_id'];
			$order_id = $params['order_id'];
		}else{
			$is_ajax = "";
			$cat_id = "";
			$order_id = "";
		}			
		$total = count($this->getTotal());
		if ( $this->getConfig('product_category')=='' ){
			return array();
		}
		$i = 0;
		foreach ( $items as $category ) { $i++;
			$category_obj		 = new stdClass();
			foreach ($order_label as $titel){
				if ( $category == $titel['value'] ){
					$category_obj->title = $titel['label'];
				}
			}
			$category_obj->id 	 = $category;
			$category_obj->orderid  = $category;
			$category_obj->count = $total;
			if ( $is_ajax ){
				$category_obj->child    = $this->getProductsOrder($order_id);
			}
			if( $i== 1 ){
				$category_obj->child    = $this->getProductsOrder($order_id);
				$category_obj->id       = $items['0'];
				$category_obj->sel  	= 'sel';
				$category_obj->orderid  = $items['0'];
			}
			$list[]= $category_obj;
		}
		return $list;
	}

	public function getProductsOrder($order_id){
		$collection = $this->getProductCollection();
		$params = Mage::app()->getRequest()->getParams();
		if( $params ){
			$is_ajax = $params['is_ajax'];
			$cat_id = $params['tab_cat_id'];
			$order_id = $params['order_id'];
			$cat_config = $params['config_categoryid'];	
		}else{
			$is_ajax = "";
			$cat_id = "";
			$order_id = "";
			$cat_config = "";
		}
		$order_by = preg_split("/\,/", $this->_config['order_tab']);
		$cat = array();
		while( count($order_by) ){
			$item = array();
			$item = trim(array_shift($order_by));
			array_push($cat, $item);
		}
		$items = array();
		$this->addReviewSummaryTemplate('sm', 'sm/tablisting/summary.phtml');
		$items = array();
		if ( Mage::registry('current_category') ){
			//is category view page.
			$current_category = Mage::registry('current_category');
			$current_category_id = $current_category->getId();
			$product_ids = $current_category->getProductCollection()->getAllIds();
			$collection->addIdFilter($product_ids);
			$category_ids = array();
		} else {
			if( $is_ajax ){
				$category_ids = preg_split("/[,\s\D]+/", $cat_config);
			} else {
				$category_ids = preg_split("/[,\s\D]+/", $this->_config['product_category']);
			}
			if (is_array($category_ids)){
				foreach ($category_ids as $i => $id) {
					if (!is_numeric($id)){
						unset($category_ids[$i]);
					}
				}
			}
		}
		if (isset($category_ids) && count($category_ids)>0) $this->_addCategoryFilter($collection, $category_ids);
		
		// Sort products in collection
		$dir = strtolower( $this->_config['product_order_dir'] );
		if (!in_array($dir, array('asc', 'desc'))){
			$dir = 'asc';
		}
	
		if( $is_ajax ){
			$attribute_to_sort = $order_id;
			switch ($attribute_to_sort){
				case 'name':
				case 'created_at':
				case 'price':
					$collection->addAttributeToSort($attribute_to_sort, $dir);
					break;
				case 'position':
					break;
				case 'random':
					$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
					break;
				case 'top_rating':
					$collection->getSelect()->order('sm_rating_summary desc');
					break;
				case 'most_reviewed':
					$collection->getSelect()->order('sm_reviews_count desc');
					break;
				case 'most_viewed':
					$collection->getSelect()->order('sm_views_count desc');
					break;
				case 'best_sales':
					$collection->getSelect()->order('sm_ordered_count desc');
					break;
			}
		} else {
			$attribute_to_sort = $cat['0'];			
			switch ($attribute_to_sort){
				case 'name':
				case 'created_at':
				case 'price':
					$collection->addAttributeToSort($attribute_to_sort, $dir);
					break;
				case 'position':
					break;
				case 'random':
					$collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
					break;
				case 'top_rating':
					$collection->getSelect()->order('sm_rating_summary desc');
					break;
				case 'most_reviewed':
					$collection->getSelect()->order('sm_reviews_count desc');
					break;
				case 'most_viewed':
					$collection->getSelect()->order('sm_views_count desc');
					break;
				case 'best_sales':
					$collection->getSelect()->order('sm_ordered_count desc');
					break;
			}	
		}
	
		if( $is_ajax ){
			$product_limitation = intval(Mage::app()->getRequest()->getParam('ajax_tablisting_start'));
			$collection->getSelect()->limit( $this->_config['product_limitation'], $product_limitation );
		} else {
			$product_limitation = intval($this->_config['product_limitation']);
			if ( $product_limitation > 0 ){
				$collection->setPageSize($product_limitation);
			}
		}
		$maxtitle = $this->getConfig('item_title_max_characs',-1);
		foreach( $collection as $k => $product ) {
			$product_obj = new stdClass();
			$product_obj->id = $product->getId();
	
			if ( $maxtitle  > 0 ){
				$product_obj->title = Mage::helper('tablisting/data')->truncate($product->getName(), $maxtitle, '');
			} else {
				$product_obj->title = $product->getName();
			}
			$description = $product->getShortDescription();
			if ( (int)$this->getConfig('item_description_striptags') == 1 ){
				$keep_tags = $this->getConfig('item_description_keeptags', '');
				$keep_tags = str_replace(array(' '), array(''), $keep_tags);
				$tmp_desc = strip_tags($description ,$keep_tags );
				$product_obj->description = $tmp_desc;
			} else {
				$product_obj->description = $description;
			}
			if (($maxchars=$this->getConfig('item_desc_max_characs',-1))>0){
				$product_obj->description = Mage::helper('tablisting/data')->truncate($product_obj->description, $maxchars, '');
			}
			$product_obj->image = (string)Mage::helper('catalog/image')->init($product, 'image')->resize($this->getConfig('item_image_width'), $this->getConfig('item_image_height'));
			$product_obj->link = $product->getProductUrl();
			$product_obj->price_html = $this->getPriceHtml($product, true);
			$product_obj->review_html = $this->getReviewsSummaryHtml($product, 'sm', true);
			
			$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
			$stock = $product->getStockItem();
			$status = $stock->getIsInStock();
			if( $status == '1' ){
				$items[$product_obj->id] = $product_obj;
			}						
			//$items[$product_obj->id] = $product_obj;
		}
		return $items;
	}	
	
	public function getScriptTags(){
		$import_str = "";
		$jsHelper = Mage::helper('core/js');
		if (null == Mage::registry('jsmart.jquery')){
			// jquery has not added yet
			if (Mage::getStoreConfigFlag('tablisting_cfg/advanced/include_jquery')){
				// if module allowed jquery.
				$import_str .= $jsHelper->includeSkinScript('sm/tablisting/js/jquery-1.8.2.min.js');
				Mage::register('jsmart.jquery', 1);
			}
		}
		if (null == Mage::registry('jsmart.jquerynoconfict')){
			// add once noConflict
			$import_str .= $jsHelper->includeSkinScript('sm/tablisting/js/jquery-noconflict.js');
			Mage::register('jsmart.jquerynoconfict', 1);
		}
		
		if (null == Mage::registry('jsmart.tablisting.js')){
			// add script for this module.
			//$import_str .= $jsHelper->includeSkinScript('sm/tablisting/js/jquery.isotope.js');
			//Mage::register('jsmart.tablisting.js', 1);
		}
		return $import_str;
	}
	
	private function _addCategoryFilter(& $collection, $category_ids){
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIsActiveFilter();
		if (count($category_ids)>0){
			$category_collection->addIdFilter($category_ids);
		}
		
		if (!Mage::helper('catalog/category_flat')->isEnabled()) { 
			$category_collection->groupByAttribute('entity_id');
		}
		
		//$category_collection->groupByAttribute('entity_id');
		$category_products = array();
		foreach ($category_collection as $category){
			$cid = $category->getId();
			if (!array_key_exists( $cid, $category_products)){
				$category_products[$cid] = $category->getProductCollection()->getAllIds();
				//Mage::log("ID: " . $cid );
				//Mage::log("collection->count(): " . count($category_products[$cid]) );
			}
		}
		$product_ids = array();
		if (count($category_products)){
			foreach ($category_products as $cp) {
				$product_ids = array_merge($product_ids, $cp);
			}
		}
		//Mage::log("merged_count: " . count($product_ids));
		$collection->addIdFilter($product_ids);
	}
	
	/*private function _addCategoryFilter(& $collection, $category_ids){
		$category_collection = Mage::getModel('catalog/category')->getCollection();
		$category_collection->addAttributeToSelect('*');
		$category_collection->addIsActiveFilter();
		$product_ids = array();

		if (count($category_ids)>0){
			$category_collection->addIdFilter($category_ids);
		}

		if (!Mage::helper('catalog/category_flat')->isEnabled()) { 
			$category_collection->groupByAttribute('entity_id');
		}

		$category_products = array();
		foreach ($category_collection as $category){
			$cid = $category->getId();
			if (!array_key_exists( $cid, $category_products)){
				$category_products[$cid] = $category->getProductCollection()->getAllIds();
			//Mage::log("ID: " . $cid );
			//Mage::log("collection->count(): " . count($category_products[$cid]) );
			}	
		}	
	}*/	
	
	private function _addViewsCount(& $collection, $views_count_alias="sm_views_count"){
		// add views_count
		$reports_event_table		= Mage::getSingleton('core/resource')->getTableName('reports/event');
		$reports_event_types_table 	= Mage::getSingleton('core/resource')->getTableName('reports/event_type');
		$collection->getSelect()
		->joinLeft(
			array("re_table" => $reports_event_table),
			"e.entity_id = re_table.object_id",
			array(
				$views_count_alias => "COUNT(re_table.event_id)"
			)
		)->joinLeft(
			array("ret_table" => $reports_event_types_table),
			"re_table.event_type_id = ret_table.event_type_id AND ret_table.event_name = 'catalog_product_view'",
			array()
		)->group('e.entity_id');
	}
	private function _addReviewsCount(& $collection, $reviews_count_alias="sm_reviews_count", $rating_summary_alias="sm_rating_summary" ){
		// add reviews_count and rating_summary
		$review_summary_table = Mage::getSingleton('core/resource')->getTableName('review/review_aggregate');
		$collection->getSelect()->joinLeft(
			array("rs_table" => $review_summary_table),
			"e.entity_id = rs_table.entity_pk_value AND rs_table.store_id=" . $this->getStoreId(),
			array(
				$reviews_count_alias  => "rs_table.reviews_count",
				$rating_summary_alias => "rs_table.rating_summary"
			)
		);
	}
	private function _addOrderedCount(& $collection, $ordered_qty_alias="sm_ordered_count"){
		$order_table = Mage::getSingleton('core/resource')->getTableName('sales/order');
		$read = Mage::getSingleton('core/resource')->getConnection ('core_read');
		$orders_active_query = $read->select()->from(array("o_table"=>$order_table), 'o_table.entity_id')->where("o_table.state<>'" . Mage_Sales_Model_Order::STATE_CANCELED . "'");
		
		$order_item_table = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
		$collection->getSelect()->joinLeft(
			array("oi_table" => $order_item_table),
			"e.entity_id=oi_table.item_id AND oi_table.order_id IN ($orders_active_query)",
			array(
				$ordered_qty_alias => "SUM(oi_table.qty_ordered)"
			)
		);
	}
	
}
