<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Catalog_Product_Edit_Tab_Deal extends Mage_Adminhtml_Block_Widget_Grid{

	public function __construct(){
		parent::__construct();
		$this->setId('deal_grid');
		$this->setDefaultSort('position');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		if ($this->getProduct()->getId()) {
			$this->setDefaultFilter(array('in_deals'=>1));
		}
	}

	protected function _prepareCollection() {
		$collection = Mage::getResourceModel('deal/deal_collection');
		if ($this->getProduct()->getId()){
			$constraint = 'related.product_id='.$this->getProduct()->getId();
			}
			else{
				$constraint = 'related.product_id=0';
			}
		$collection->getSelect()->joinLeft(
			array('related'=>$collection->getTable('deal/deal_product')),
			'related.deal_id=main_table.entity_id AND '.$constraint,
			array('position')
		);
		$this->setCollection($collection);
		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareMassaction(){
		return $this;
	}

	protected function _prepareColumns(){
		$this->addColumn('in_deals', array(
			'header_css_class'  => 'a-center',
			'type'  => 'checkbox',
			'name'  => 'in_deals',
			'values'=> $this->_getSelectedDeals(),
			'align' => 'center',
			'index' => 'entity_id'
		));
		$this->addColumn('name', array(
			'header'=> Mage::helper('deal')->__('Deal Name'),
			'align' => 'left',
			'index' => 'name',
		));
		$this->addColumn('position', array(
			'header'		=> Mage::helper('deal')->__('Position'),
			'name'  		=> 'position',
			'width' 		=> 60,
			'type'		=> 'number',
			'validate_class'=> 'validate-number',
			'index' 		=> 'position',
			'editable'  	=> true,
		));
	}

	protected function _getSelectedDeals(){
		$deals = $this->getProductDeals();
		if (!is_array($deals)) {
			$deals = array_keys($this->getSelectedDeals());
		}
		return $deals;
	}
 
	public function getSelectedDeals() {
		$deals = array();
		$selected = Mage::helper('deal/product')->getSelectedDeals(Mage::registry('current_product'));
		if (!is_array($selected)){
			$selected = array();
		}
		foreach ($selected as $deal) {
			$deals[$deal->getId()] = array('position' => $deal->getPosition());
		}
		return $deals;
	}

	public function getRowUrl($item){
		return '#';
	}

	public function getGridUrl(){
		return $this->getUrl('*/*/dealsGrid', array(
			'id'=>$this->getProduct()->getId()
		));
	}

	public function getProduct(){
		return Mage::registry('current_product');
	}

	protected function _addColumnFilterToCollection($column){
		if ($column->getId() == 'in_deals') {
			$dealIds = $this->_getSelectedDeals();
			if (empty($dealIds)) {
				$dealIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$dealIds));
			} 
			else {
				if($dealIds) {
					$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$dealIds));
				}
			}
		} 
		else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}
}