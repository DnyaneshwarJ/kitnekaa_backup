<?php
class Kitnekaa_Notification_Block_Notification extends Mage_Core_Block_Template {
	public function getUnreadCountUrl() {
		return $this->getUrl ( '*/*/getUnreadCount/' );
	}
}
