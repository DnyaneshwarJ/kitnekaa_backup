<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Adminhtml_Observer{

	protected function _canAddTab($product){
		if ($product->getId()){
			return true;
		}
		if (!$product->getAttributeSetId()){
			return false;
		}
		$request = Mage::app()->getRequest();
		if ($request->getParam('type') == 'configurable'){
			if ($request->getParam('attribtues')){
				return true;
			}
		}
		return false;
	}

	public function addDealBlock($observer){
		$block = $observer->getEvent()->getBlock();
		$product = Mage::registry('product');
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs && $this->_canAddTab($product)){
			$block->addTab('deals', array(
				'label' => Mage::helper('deal')->__('Deals'),
				'url'   => Mage::helper('adminhtml')->getUrl('adminhtml/deal_deal_catalog_product/deals', array('_current' => true)),
				'class' => 'ajax',
			));
		}
		return $this;
	}

	public function saveDealData($observer){
		$post = Mage::app()->getRequest()->getPost('deals', -1);
		if ($post != '-1') {
			$post = Mage::helper('adminhtml/js')->decodeGridSerializedInput($post);
			$product = Mage::registry('product');
			$dealProduct = Mage::getResourceSingleton('deal/deal_product')->saveProductRelation($product, $post);
		}
		return $this;
	}}