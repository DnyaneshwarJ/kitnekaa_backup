<?php

class Vtigercrm_Quotation_QuotationController extends Mage_Core_Controller_Front_Action
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
		
		$this->getLayout()->getBlock('head')->setTitle($this->__('My Quotation'));
		$this->renderLayout();
	}
	
	public function vtigerAction($pass){
		//var_dump(Mage::getStoreConfig( 'vtigercrm_quotation/' . 'vtiger/apiurl', 1 ));
		//var_dump($this->getConf( 'vtiger/apiurl'));exit;
		//var_dump($this->getRequest()->getParam('id'));exit;
		//$data = Mage::getSingleton('kithnekaa_solr/webservice');
		$data = Mage::getSingleton('vtigercrm_webservice/webservice');
		var_dump($data);
		//var_dump(Mage::registry('challengeToken'));exit;
		//var_dump(Mage::registry('challengeToken'));exit;
		//var_dump($data->getChallenge());
		//var_dump($data->login('admin'));
		
	}
	
	public function acceptAction()
	{
		$quote_id = $this->getRequest()->getParam('id');
		
		try {
			$this->vtigerConnect = Mage::getSingleton('kithnekaa_solr/webservice');			
			$output = Mage::helper('vtigercrm_quotation/data')->retrieveData('4x'.$quote_id);
			$output = $output['result'];
			$output['quotestage'] = "Accepted";
			$output['productid'] = $output['LineItems'][0]['productid'];
			
			$data = $this->vtigerConnect->update($output, 'Quotes');
			
			$so_response = $this->createSalesOrderAction($quote_id, $output);
						
			if($so_response){
				Mage::getSingleton('customer/session')->addSuccess(
						$this->__('Quotation successfully accepted.')
				);
				
				
			} else {
				Mage::getSingleton('customer/session')->addError(
						$this->__('An error occurred while accepting the quotation.')
				);
			}
			
		} catch (Mage_Core_Exception $e) {
			Mage::getSingleton('customer/session')->addError(
					$this->__('An error occurred while accepting the quotation: %s', $e->getMessage())
			);
		} catch (Exception $e) {
			Mage::getSingleton('customer/session')->addError(
					$this->__('An error occurred while accepting the quotation.')
			);
		}
	
		Mage::helper('wishlist')->calculate();
	
		$this->_redirectReferer(Mage::getUrl('*/*'));
	}
	
	public function createSalesOrderAction($quote_id, $quoteDetails){
		$address_elements = array('street', 'city', 'state', 'code', 'country', 'pobox');
		
		$salesOrderParam = array();
		
		$salesOrderParam['subject'] = "New SalesOrder";
		$salesOrderParam['account_id'] = $quoteDetails['account_id'];
		$salesOrderParam['assigned_user_id'] = $quoteDetails['assigned_user_id'];
		$salesOrderParam['productid'] = $quoteDetails['productid'];
		$salesOrderParam['LineItems'] = $quoteDetails['LineItems'];
		
		foreach($address_elements as $address_elements_value){
			$salesOrderParam['bill_'.$address_elements_value] = $quoteDetails['bill_'.$address_elements_value];
			$salesOrderParam['ship_'.$address_elements_value] = $quoteDetails['ship_'.$address_elements_value];
		}
		
		$salesOrderParam['invoicestatus'] = "Autocreated";
		$salesOrderParam['quote_id'] = '4x'.$quote_id;
		$salesOrderParam['sostatus'] = "Created";
		
		$response = $this->vtigerConnect->create($salesOrderParam, 'SalesOrder');
		$response = Zend_Json_Decoder::decode($response);
		
		if($response['success']){
			return true;	
		} else {
			
			$quoteDetails['quotestage'] = "Created";
			$this->vtigerConnect->update($quoteDetails, 'Quotes');
			return false;
		}
		
	}
	
	public function invoiceAction(){
		
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('catalog/session');
		
		$this->getLayout()->getBlock('head')->setTitle($this->__('Invoice'));
		$this->renderLayout();
		
		
	}
}