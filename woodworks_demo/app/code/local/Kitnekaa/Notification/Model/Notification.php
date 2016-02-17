<?php
class Kitnekaa_Notification_Model_Notification extends Mage_Core_Model_Abstract {
	const CACHE_TAG = 'notification';
	
	/**
	 * Model event prefix
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'notification';
	
	/**
	 * Name of the event object
	 *
	 * @var string
	 */
	protected $_eventObject = 'notification';
	protected function _construct() {
		$this->_init ( 'notification' );
	}
	public function sendMsg($id, $msg) {
		echo "id: $id" . PHP_EOL;
		echo "data: {\n";
		echo "data: \"msg\": \"$msg\", \n";
		echo "data: \"id\": $id\n";
		echo "data: }\n";
		echo PHP_EOL;
		ob_flush ();
		flush ();
	}
	public function getNotificationCount($receiverId) {
		$requestPar = array (
				"receiver_id" => $receiverId 
		);
		
		$result = $this->getCurl ( "notification_count", $requestPar, 'GET' );
		$resultCode = Zend_Http_Response::extractCode ( $result );
		
		$resultMessage = Zend_Http_Response::extractMessage ( $result );
		$resultBody = Zend_Http_Response::extractBody ( $result );
		
		$resultDecoded = @ Zend_Json::decode ( $resultBody );
		
		$countData = @ Zend_Json::decode ( $resultDecoded ['result'] );
		
		return $countData ['count'];
	}
	public function getNotifications($receiverId) {
		$requestPar = array (
				"receiver_id" => $receiverId
		);
	
		$result = $this->getCurl ( "list", $requestPar, 'GET' );
		$resultCode = Zend_Http_Response::extractCode ( $result );
	
		$resultMessage = Zend_Http_Response::extractMessage ( $result );
		$resultBody = Zend_Http_Response::extractBody ( $result );
	
		$resultDecoded = @ Zend_Json::decode ( $resultBody );
	
		
		return $resultDecoded ['result'];
	}
	private function getCurl($method, $requestPar = array(), $requestType) {
		$httpAdapter = new Varien_Http_Adapter_Curl ();
		$apiUrl = "http://localhost/latte/notify_api/v1/notification/".$method."?".http_build_query($requestPar);
		$config = array (
				'timeout' => 60 
		);
		$httpAdapter->setConfig ( $config );
		$apiUrl = $apiUrl . $method;
		$options = array (
				CURLOPT_RETURNTRANSFER => true 
		);
		$httpAdapter->setOptions ( $options );
		
		if ($requestType == 'GET') {
			$apiUrl = $apiUrl . "?";
			
			foreach ( $requestPar as $key => $value ) {
				$apiUrl .= '&' . $key . "=" . $value;
			}
			$httpAdapter->write ( Zend_Http_Client::GET, $apiUrl, '1.1' );
		} elseif ($requestType == 'POST') {
			
			foreach ( $requestPar as $key => $value ) {
				if ($value !== reset ( $requestPar )) {
					$key = "&" . $key;
				}
				$requestQuery .= $key . "=" . $value;
			}
			
			$httpAdapter->write ( Zend_Http_Client::POST, $apiUrl, '1.1', array (), $requestQuery );
		}
		$response = $httpAdapter->read ();
		$httpAdapter->close ();
		return $response;
	}
}