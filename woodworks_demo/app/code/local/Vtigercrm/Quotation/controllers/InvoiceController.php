<?php

class Vtigercrm_Quotation_InvoiceController extends Mage_Core_Controller_Front_Action
{	
	private $vtigerConnect;
	
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}
	
	public function preDispatch()
	{
		parent::preDispatch();
	
		if (!Mage::getSingleton('customer/session')->authenticate($this)) {
			$this->setFlag('', 'no-dispatch', true);
		}
	}
	
	public function indexAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('catalog/session');
		
		$this->getLayout()->getBlock('head')->setTitle($this->__('Invoice'));
		$this->renderLayout();
	}
	
}