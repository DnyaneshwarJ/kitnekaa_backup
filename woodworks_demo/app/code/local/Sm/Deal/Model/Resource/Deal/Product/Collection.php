<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
class Sm_Deal_Model_Resource_Deal_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection{

	protected $_joinedFields = false;

	public function joinFields(){
		if (!$this->_joinedFields){
			$this->getSelect()->join(
				array('related' => $this->getTable('deal/deal_product')),
				'related.product_id = e.entity_id',
				array('position')
			);
			$this->_joinedFields = true;
		}
		return $this;
	}

	public function addDealFilter($deal){
		if ($deal instanceof Sm_Deal_Model_Deal){
			$deal = $deal->getId();
		}
		if (!$this->_joinedFields){
			$this->joinFields();
		}
		$this->getSelect()->where('related.deal_id = ?', $deal);
		return $this;
	}

	public function joinFieldsdeal(){
		
			$this->getSelect()->join(
				array('deal' => $this->getTable('deal/deal')),
				'deal.entity_id = related.deal_id',
				array('deal.end_date','deal.start_date','deal.name')
			);
			$this->_joinedFields = true;
		
		return $this;
	}
	
	public function addFilter($filterName,$filtervalue,$condition='='){
		$deal = "";
		if ($deal instanceof Sm_Deal_Model_Deal){
			$deal = $deal->getId();
		}
		if (!$this->_joinedFields){
			$this->joinFields();
		}
		 $this->getSelect()->where('deal.'.$filterName.' '.$condition.' ?', $filtervalue);
		return $this;
	}
	
	public function OrderbyAdd($orderName,$ordervalue){
		$deal = "";
		if ($deal instanceof Sm_Deal_Model_Deal){
			$deal = $deal->getId();
		}
		$this->getSelect()->order('deal.'.$orderName.' '.$ordervalue);
		
		return $this;
	}
	
}