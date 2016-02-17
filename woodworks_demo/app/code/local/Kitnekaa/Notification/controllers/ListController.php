<?php
class Kitnekaa_Notification_ListController extends Mage_Core_Controller_Front_Action {
	public function indexAction() 
	{
		$notify = Mage::getSingleton ( 'kitnekaa_notification/notification' );
		$list = $notify->getNotifications ( 2 );
		echo "<pre>";
		print_r($list);
		echo "Hello";die();
	}
	
}