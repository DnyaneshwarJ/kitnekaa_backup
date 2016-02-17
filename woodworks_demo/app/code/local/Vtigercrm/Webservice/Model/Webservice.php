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
class Vtigercrm_Webservice_Model_Webservice extends Mage_Core_Model_App
{
	/*
	public function _construct() {
		$this->_init('vtigercrm_webservice/webservice');
	}
	*/
	private $sessionName;
	private $username = "admin";
	private $accessKeyDB = "nQKgiriwWb8wfEWB";
	private $accessKey;
	private $assigned_user_id = 1;
	private $challengeToken;
	
	
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
			throw new Exception('Something went wrong with retrieving entity ID '.$entityID.' operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
		}
		 
		return $resultBody;
	}
	
	public function createTicket($requestPar){
	  $requestPar['assigned_user_id']=$requestPar['assigned_user_id']!=""?$requestPar['assigned_user_id']:'1';
	  $requestPar['ticketstatus']=$requestPar['ticketstatus']!=""?$requestPar['ticketstatus']:'Open';
	  $requestPar['ticket_title']=$requestPar['ticket_title']!=""?$requestPar['ticket_title']:'Online query from kitnekaa.com';
	  
	        $element = Zend_Json::encode($requestPar);
	        
	        $requestPar = array("operation" => "create", "sessionName" => $this->sessionName, "elementType" => "HelpDesk", "element" => $element);
	
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
	         throw new Exception('Something went wrong with createTicket operation! errorCode: '.
	           $resultCode .', errorMessage: '. $resultMessage);
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
			throw new Exception('Something went wrong with create'.$entityType.' operation! errorCode: '.
					$resultCode .', errorMessage: '. $resultMessage);
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
		}
		
		return $resultBody;
	}
	
	private function getCurl($requestPar = array(), $requestType){

		$httpAdapter = new Varien_Http_Adapter_Curl();
		$apiUrl = "http://localhost/vtigercrm/webservice.php";
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