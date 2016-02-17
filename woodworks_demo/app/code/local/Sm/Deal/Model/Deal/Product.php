<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Deal_Product extends Mage_Core_Model_Abstract{

	protected function _construct(){
		$this->_init('deal/deal_product');
	}

	public function saveDealRelation($deal){
		$data = $deal->getProductsData();
		if (!is_null($data)) {
			$this->_getResource()->saveDealRelation($deal, $data);
		}
		return $this;
	}

	public function getProductCollection($deal){
		$collection = Mage::getResourceModel('deal/deal_product_collection')
			->addDealFilter($deal);
		return $collection;
	}
}