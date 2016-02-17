<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
class Sm_Deal_Block_List extends Mage_Core_Block_Template{

 	public function __construct(){
		parent::__construct();
 		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$Deals  = Mage::getResourceModel('deal/deal_product_collection')->joinFields()->joinFieldsdeal()
						->addFilter('status', '1', "=")
						->addFilter('end_date', $todayDate, ">");
						
		if(Mage::getStoreConfig('deal/deal/orderby') == 1)
			{
			$Deals->OrderbyAdd('start_date','asc');
			}
			else{
			$Deals->OrderbyAdd('end_date','asc');
			}
		$this->setDeals($Deals);
	}

	protected function _prepareLayout(){
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager', 'deal.deal.html.pager');
			$pager->setAvailableLimit(array(8=>8,16=>16,32=>32,'all'=>'all'));
			$pager->setCollection($this->getDeals());
		$this->setChild('pager', $pager);
		$this->getDeals()->load();				
		return $this;
	}
}