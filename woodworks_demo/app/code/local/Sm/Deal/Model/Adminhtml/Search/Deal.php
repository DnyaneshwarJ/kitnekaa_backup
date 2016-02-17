<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Adminhtml_Search_Deal extends Varien_Object{

	public function load(){
		$arr = array();
		if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
			$this->setResults($arr);
			return $this;
		}
		$collection = Mage::getResourceModel('deal/deal_collection')
			->addFieldToFilter('name', array('like' => $this->getQuery().'%'))
			->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
			->load();
		foreach ($collection->getItems() as $deal) {
			$arr[] = array(
				'id'=> 'deal/1/'.$deal->getId(),
				'type'  => Mage::helper('deal')->__('Deal'),
				'name'  => $deal->getName(),
				'description'   => $deal->getName(),
				'url' => Mage::helper('adminhtml')->getUrl('*/deal_deal/edit', array('id'=>$deal->getId())),
			);
		}
		$this->setResults($arr);
		return $this;
	}
}