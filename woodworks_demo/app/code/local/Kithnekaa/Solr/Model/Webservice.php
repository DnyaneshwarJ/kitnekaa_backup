<?php

/**
 * Vtigercrm_Webservice
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category    Vtigercrm
 * @package     Vtigercrm_Webservice
 */
class Kithnekaa_Solr_Model_Webservice extends Mage_Core_Model_App
{
	/*
	public function __construct() {
		$this->_init('solr/webservice');
	}
	*/
	private $sessionName;
	private $username;
	private $accessKeyDB;
	private $accessKey;
	private $assigned_user_id = 1;
	private $challengeToken;
	private $apiurl;
	
	public function __construct(){
		$session = Mage::getSingleton("core/session");
		$this->sessionName = $session->getData("vtigerSessionName");
		$this->challengeToken = $session->getData("vtigerChallengeToken");
		$storeId = Mage::app()->getStore()->getId();
		
		$this->accessKeyDB = Mage::getStoreConfig( 'vtigercrm_quotation/' . 'vtiger/accessKeyDB', $storeId );
		$this->username = Mage::getStoreConfig( 'vtigercrm_quotation/' . 'vtiger/username', $storeId );
		$this->assigned_user_id = Mage::getStoreConfig( 'vtigercrm_quotation/' . 'vtiger/assigned_user_id', $storeId );
		$this->apiurl = Mage::getStoreConfig( 'vtigercrm_quotation/' . 'vtiger/apiurl', $storeId );
	}
	
	public function retrieve($entityId){
		$requestPar = array("operation" => "retrieve", "sessionName" => $this->sessionName, "id" => $entityId);
		
		$result = $this->getCurl($requestPar, 'GET');
		$resultCode = Zend_Http_Response::extractCode($result);
		 
		$resultMessage = Zend_Http_Response::extractMessage($result);
		$resultBody =  Zend_Http_Response::extractBody($result);
		 
		$resultDecoded = @ Zend_Json::decode($resultBody);
		if (null === $resultDecoded) {
			throw new Exception('No response returned from Vtiger server!');
		}
		// See "ErrorObject" on manual
		if (null !== $resultDecoded['success'] && false === $resultDecoded['success']) {
			/*
			throw new Exception('Something went wrong with retrieving entity ID '.$entityID.' operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
					*/
		}
		 
		return $resultBody;
	}
	
	 
	public function create($requestPar, $entityType){

		$element = Zend_Json::encode($requestPar);

		$requestPar = array("operation" => "create", "sessionName" => $this->sessionName, "elementType" => $entityType, "element" => $element);
		
		$result = $this->getCurl($requestPar, 'POST');
		$resultCode = Zend_Http_Response::extractCode($result);
		 
		$resultMessage = Zend_Http_Response::extractMessage($result);
		$resultBody =  Zend_Http_Response::extractBody($result);
		 
		$resultDecoded = @ Zend_Json::decode($resultBody);
		if (null === $resultDecoded) {
			throw new Exception('No response returned from Vtiger server!');
		}
		// See "ErrorObject" on manual
		if (null !== $resultDecoded['success'] && false === $resultDecoded['success']) {
			/*
			throw new Exception('Something went wrong with create'.$entityType.' operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
			*/		
		}
		 
		return $resultBody;
	}
	 
	public function login($username = "admin"){
		$this->accessKey = md5($this->challengeToken.$this->accessKeyDB);
		
		$requestPar = array("operation" => "login", "username" => $username, "accessKey" => $this->accessKey);
		
		$result = $this->getCurl($requestPar, 'POST');

		$resultCode = Zend_Http_Response::extractCode($result);
		
		$resultMessage = Zend_Http_Response::extractMessage($result);
		$resultBody =  Zend_Http_Response::extractBody($result);

		$resultDecoded = @ Zend_Json::decode($resultBody);		
		if (null === $resultDecoded) {
			throw new Exception('No response returned from Vtiger server!');
		}

		// See "ErrorObject" on manual
		if (null !== $resultDecoded['success'] && false === $resultDecoded['success']) {
			throw new Exception('Something went wrong with login operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
		}
		
		if($resultCode == 200 && $resultMessage == "OK"){
			$this->sessionName = $resultDecoded['result']['sessionName'];
			$session = Mage::getSingleton("core/session");
			$session->setData("vtigerSessionName", $resultDecoded['result']['sessionName']);
		}
		
		return $resultBody;
		
	}
	
	public function getChallenge($username = "admin"){

		$requestPar = array('operation' => 'getchallenge','username' => 'admin');
		$result = $this->getCurl($requestPar, 'GET');

		$resultCode = Zend_Http_Response::extractCode($result);

		$resultMessage = Zend_Http_Response::extractMessage($result);
		$resultBody = Zend_Http_Response::extractBody($result);
		
		$resultDecoded = @ Zend_Json::decode($resultBody);
		if (null === $resultDecoded) {
			throw new Exception('No response returned from Vtiger server!');
		}
		// See "ErrorObject" on manual
		if (null !== $resultDecoded['success'] && false === $resultDecoded['success']) {
			throw new Exception('Something went wrong with getchallenge operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
		}
		
		if($resultCode == 200 && $resultMessage == "OK"){
			$this->challengeToken = $resultDecoded['result']['token'];
			$session = Mage::getSingleton("core/session");
			$session->setData("vtigerChallengeToken", $resultDecoded['result']['token']);
		}
		
		return $resultBody;
	}
	
	private function getCurl($requestPar = array(), $requestType){

		$httpAdapter = new Varien_Http_Adapter_Curl();
		$apiUrl = $this->apiurl;
		$config = array('timeout' => 60);
		$httpAdapter->setConfig($config);
		
		$options = array(CURLOPT_RETURNTRANSFER => true);
		$httpAdapter->setOptions($options);

		if($requestType == 'GET'){
			$apiUrl = $apiUrl."?";
			
			foreach($requestPar as $key => $value){
				$apiUrl.= '&'.$key."=".$value;
			}
			
			$httpAdapter->write(Zend_Http_Client::GET, $apiUrl, '1.1');
		} elseif($requestType == 'POST') {
			$requestQuery = "";
			foreach($requestPar as $key => $value){
				if($value !== reset($requestPar)){
					
					$key = "&".$key;	
				}
				
				$requestQuery .= $key."=".$value;			
			}

			$httpAdapter->write(Zend_Http_Client::POST, $apiUrl, '1.1', array(), $requestQuery);
		}
		$response = $httpAdapter->read();
		$httpAdapter->close();
		return $response;

	}
}