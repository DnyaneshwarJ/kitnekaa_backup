<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Adminhtml_Deal_DealController extends Sm_Deal_Controller_Adminhtml_Deal{

	protected function _initDeal(){
		$dealId  = (int) $this->getRequest()->getParam('id');
		$deal	= Mage::getModel('deal/deal');
		if ($dealId) {
			$deal->load($dealId);
		}
		Mage::register('current_deal', $deal);
		return $deal;
	}

	public function indexAction() {
		$this->loadLayout();
		$this->_title(Mage::helper('deal')->__('Deals'))
			 ->_title(Mage::helper('deal')->__('Deals'));
		$this->renderLayout();
	}

	public function gridAction() {
		$this->loadLayout()->renderLayout();
	}

	public function editAction() {
		$dealId	= $this->getRequest()->getParam('id');
		$deal  	= $this->_initDeal();
		if ($dealId && !$deal->getId()) {
			$this->_getSession()->addError(Mage::helper('deal')->__('This deal no longer exists.'));
			$this->_redirect('*/*/');
			return;
		}
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$deal->setData($data);
		}
		Mage::register('deal_data', $deal);
		$this->loadLayout();
		$this->_title(Mage::helper('deal')->__('Deal'))
			 ->_title(Mage::helper('deal')->__('Deal'));
		if ($deal->getId()){
			$this->_title($deal->getName());
		}
		else{
			$this->_title(Mage::helper('deal')->__('Add deal'));
		}
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) { 
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); 
		}
		$this->renderLayout();
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost('deal')) {
			try {
				$deal = $this->_initDeal();
				$deal->addData($data);
				$products = $this->getRequest()->getPost('products', -1);
				if ($products != -1) {
					$deal->setProductsData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($products));
				}
				$deal->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('deal')->__('Deal was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $deal->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} 
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			catch (Exception $e) {
				Mage::logException($e);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('There was a problem saving the deal.'));
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('Unable to find deal to save.'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$deal = Mage::getModel('deal/deal');
				$deal->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('deal')->__('Deal was successfully deleted.'));
				$this->_redirect('*/*/');
				return; 
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('There was an error deleteing deal.'));
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				Mage::logException($e);
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('Could not find deal to delete.'));
		$this->_redirect('*/*/');
	}

	public function massDeleteAction() {
		$dealIds = $this->getRequest()->getParam('deal');
		if(!is_array($dealIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('Please select deal to delete.'));
		}
		else {
			try {
				foreach ($dealIds as $dealId) {
					$deal = Mage::getModel('deal/deal');
					$deal->setId($dealId)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('deal')->__('Total of %d deal were successfully deleted.', count($dealIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('There was an error deleteing deal.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction(){
		$dealIds = $this->getRequest()->getParam('deal');
		if(!is_array($dealIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('Please select deal.'));
		} 
		else {
			try {
				foreach ($dealIds as $dealId) {
				$deal = Mage::getSingleton('deal/deal')->load($dealId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d deal were successfully updated.', count($dealIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('deal')->__('There was an error updating deal.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}

	public function productsAction(){
		$this->_initDeal();
		$this->loadLayout();
		$this->getLayout()->getBlock('deal.edit.tab.product')
			->setDealProducts($this->getRequest()->getPost('deal_products', null));
		$this->renderLayout();
	}

	public function productsgridAction(){
		$this->_initDeal();
		$this->loadLayout();
		$this->getLayout()->getBlock('deal.edit.tab.product')
			->setDealProducts($this->getRequest()->getPost('deal_products', null));
		$this->renderLayout();
	}

	public function exportCsvAction(){
		$fileName   = 'deal.csv';
		$content	= $this->getLayout()->createBlock('deal/adminhtml_deal_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportExcelAction(){
		$fileName   = 'deal.xls';
		$content	= $this->getLayout()->createBlock('deal/adminhtml_deal_grid')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	public function exportXmlAction(){
		$fileName   = 'deal.xml';
		$content	= $this->getLayout()->createBlock('deal/adminhtml_deal_grid')->getXml();
		$this->_prepareDownloadResponse($fileName, $content);
	}
}