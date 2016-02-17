<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Block_Adminhtml_Deal extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct(){
		$this->_controller 		= 'adminhtml_deal';
		$this->_blockGroup 		= 'deal';
		$this->_headerText 		= Mage::helper('deal')->__('Manage Deals');
		$this->_addButtonLabel 	= Mage::helper('deal')->__('Add Deal');
		parent::__construct();
	}
}