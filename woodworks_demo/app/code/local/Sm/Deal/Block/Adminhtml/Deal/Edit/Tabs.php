<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{

	public function __construct(){
		parent::__construct();
		$this->setId('deal_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('deal')->__('Deal Information'));
	}

	protected function _beforeToHtml(){
	
		$this->addTab('products', array(
			'label' => Mage::helper('deal')->__('Associated products'),
			'url'   => $this->getUrl('*/*/products', array('_current' => true)),
   			'class'	=> 'ajax'
		));
		$this->addTab('form_deal', array(
			'label'		=> Mage::helper('deal')->__('Deal'),
			'title'		=> Mage::helper('deal')->__('Deal'),
			'content' 	=> $this->getLayout()->createBlock('deal/adminhtml_deal_edit_tab_form')->toHtml(),
		));

		if (!Mage::app()->isSingleStoreMode()){
			$this->addTab('form_store_deal', array(
				'label'		=> Mage::helper('deal')->__('Store views'),
				'title'		=> Mage::helper('deal')->__('Store views'),
				'content' 	=> $this->getLayout()->createBlock('deal/adminhtml_deal_edit_tab_stores')->toHtml(),
			));
		}
		
		return parent::_beforeToHtml();
	}
}