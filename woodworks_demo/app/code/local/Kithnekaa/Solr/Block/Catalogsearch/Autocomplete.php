<?php
/**
 * JeroenVermeulen_Solarium
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category    JeroenVermeulen
 * @package     JeroenVermeulen_Solarium
 * @copyright   Copyright (c) 2014 Jeroen Vermeulen (http://www.jeroenvermeulen.eu)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Kithnekaa_Solr_Block_Catalogsearch_Autocomplete extends Mage_CatalogSearch_Block_Autocomplete
{

	protected function _toHtml()
	{
		$html = '';
	
		if (!$this->_beforeToHtml()) {
			return $html;
		}
	
		$suggestData = $this->getSuggestData();
		if (!($count = count($suggestData))) {
			return $html;
		}
	
		$count--;
	
		$html = '<ul><li style="display:none"></li>';
		$total_count_results = $suggestData['num_of_results'];
		unset($suggestData['num_of_results']);
		foreach ($suggestData['results'] as $index => $item) {
			if ($index == 0) {
				$item['row_class'] .= ' first';
			}
	
			if ($index == $count) {
				$item['row_class'] .= ' last';
			}
	
			$html .=  '<li class="'.$item['row_class'].'"><a href="'.$suggestData['product_collection'][$item['product_id']].'" style="text-decoration:none;color:#000">'
					.$this->escapeHtml($item['product']." ".$item['category']).'</a></li>';
		}
		if($total_count_results){
			$queryString = $this->helper('catalogsearch')->getQueryText();
			$html .= '<li title='.$queryString.'>Total number of results  '.$total_count_results.'</li>';		
		} else {
			$html .= '<li>No results found</li>';	
		}
		
			$html.= '</ul>';
	
		return $html;
	}
	
	
	public function _toJson(){
		$output = $this->getSuggestDataForShippingList();
		$output = Zend_Json_Encoder::encode($output);
		return $output;	
	}
		
    /**
     * Sanitize result to standard core functionality
     * @return array|null
     */
    public function getSuggestData()
    {//$catquery = $this->helper('catalogsearch')->getQueryText('cat');
    //print_r($catquery);exit;
        if ( ! $this->_suggestData ) {
        	$kithnekaa_model = Mage::getSingleton('kithnekaa_solr/index');
        	$query = $this->helper('catalogsearch')->getQueryText();
        	$data = $kithnekaa_model->getSuggestData($query);  
            $this->_suggestData = $data;
        }
        
        return $this->_suggestData;
    }
    
    public function getProductCollection($storeId, $productIds){
    	$root_category_id = Mage::app()->getStore($storeId)->getRootCategoryId();
    		
    	$product_details = array();
    		
    	$product_category = "";
    		
    	$product = Mage::getModel('catalog/product')->load(196);
    	var_dump($product->getFeatured());
    }
    
    public function getSuggestDataForShippingList(){
    	if ( ! $this->_suggestData ) {
    		$query = $this->helper('catalogsearch')->getQueryText();
    		$counter = 0;
    		$data = array();
    		$storeId = Mage::app()->getStore()->getId();
    		$engine = Mage::getSingleton('jeroenvermeulen_solarium/engine');
    		//Commented by Anthony
    		//$facet = $engine->getAutoSuggestions( $storeId, $query );
    		//Added by Anthony
    		$queryString = "q=*".$query."*&wt=json";
    		$facet = $engine->getAutoSuggestionsNestIncubate($storeId, $queryString);
    		
    		foreach ( $facet['docs'] as $value) {
    			$_data = array(
    					'product_id' => $value['product_id'],
    					'product_name' => $value['product_name'],
    			);
    			$data[] = $_data;
    		}
    	
    		$this->_suggestData = $data;
    	}
    	
    	return $this->_suggestData;
    }
    
    public function getSuggestData_(){

    	if ( ! $this->_suggestData ) {
    		$query = $this->helper('catalogsearch')->getQueryText();
    		$counter = 0;
    		$data = array();
    		$storeId = Mage::app()->getStore()->getId();
    		$engine = Mage::getSingleton('jeroenvermeulen_solarium/engine');
    		$facet = $engine->getAutoSuggestions( $storeId, $query );
    	
    		foreach ( $facet as $value => $count ) {
    			$_data = array(
    					'title' => $value,
    					'row_class' => ( ++$counter ) % 2 ? 'odd' : 'even',
    					'num_of_results' => $count
    			);
    			$data[] = $_data;
    		}
    		$this->_suggestData = $data;
    	}
    	return $this->_suggestData;
    }
    
    
}