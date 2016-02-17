<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

include_once ("Mage/Adminhtml/controllers/Catalog/ProductController.php");
class Sm_Deal_Adminhtml_Deal_Deal_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController{

	protected function _construct(){
		$this->setUsedModuleName('Sm_Deal');
	}

	public function dealsAction(){
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('product.edit.tab.deal')
			->setProductDeals($this->getRequest()->getPost('product_deals', null));
		$this->renderLayout();
	}

	public function dealsGridAction(){
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('product.edit.tab.deal')
			->setProductDeals($this->getRequest()->getPost('product_deals', null));
		$this->renderLayout();
	}
}