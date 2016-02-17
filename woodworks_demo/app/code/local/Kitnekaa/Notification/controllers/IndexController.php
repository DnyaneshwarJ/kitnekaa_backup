<?php
class Kitnekaa_Notification_IndexController extends Mage_Core_Controller_Front_Action {
	public function indexAction() {
		$x = $this->getLayout()->createBlock('kitnekaa_notification/notification');
		var_dump($x->getUnreadCountUrl());
	}
	public function getUnreadCountAction() {
		$notify = Mage::getSingleton ( 'kitnekaa_notification/notification' );
		$notify = Mage::getSingleton ( 'kitnekaa_notification/notification' );
		$count = $notify->getNotificationCount ( 2 );
			
		$notify->sendMsg ( time () - $startedAt, $count );
			die();
		$startedAt = time ();
		do {
			// Cap connections at 10 seconds. The browser will reopen the connection on close
			if ((time () - $startedAt) > 50) {
				die ();
			}
			$notify = Mage::getSingleton ( 'kitnekaa_notification/notification' );
			$count = $notify->getNotificationCount ( 2 );
			
			$notify->sendMsg ( time () - $startedAt, $count );
			sleep ( 5 );
		} while ( true );
	}
	public function getAllNotificationsAction() {
	}
}