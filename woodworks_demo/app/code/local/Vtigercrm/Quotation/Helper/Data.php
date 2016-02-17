<?php

class Vtigercrm_Quotation_Helper_Data extends Mage_Core_Helper_Abstract
{	
	private $webservice_connect;
	
	public function __construct(){
		$this->webservice_connect = Mage::getSingleton('kithnekaa_solr/webservice'); 	
	}
	
	public function getChallenge(){
		$webservice_connect = $this->webservice_connect;
		$webservice_connect->getChallenge();
	}
	
	public function login($username = "admin"){
		$webservice_connect = $this->webservice_connect;
		$webservice_connect->login($username);
	}
	
    public function retrieveData($entity_id){
    	$webservice_connect = $this->webservice_connect;
    	$data = $webservice_connect->retrieve($entity_id);
    	$output = Zend_Json_Decoder::decode($data);
    	return $output;
    }
    
    
    public function test(){
    	//$data = Mage::getSingleton('vtigercrm_webservice/webservice');
    	$v = Mage::getSingleton('quotation/quotation');
    	return $v;
    }
    
   

}