<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Helper_Product extends Sm_Deal_Helper_Data{

	public function getSelectedDeals(Mage_Catalog_Model_Product $product){
		if (!$product->hasSelectedDeals()) {
			$deals = array();
			foreach ($this->getSelectedDealsCollection($product) as $deal) {
				$deals[] = $deal;
			}
			$product->setSelectedDeals($deals);
		}
		return $product->getData('selected_deals');
	}

	public function getSelectedDealsCollection(Mage_Catalog_Model_Product $product){
		$collection = Mage::getResourceSingleton('deal/deal_collection')
			->addProductFilter($product);
		return $collection;
	}
}