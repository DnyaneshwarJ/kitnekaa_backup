<?php

class Vitgercrm_Quotation_Model_Quotation extends Mage_Core_Model_Abstract
{
	
	protected function _construct() {
		parent::_construct();
		$this->_init('quotation/quotation');
	}
	
	public function getSuggestDataShop(){
		return "hello";
	}
}