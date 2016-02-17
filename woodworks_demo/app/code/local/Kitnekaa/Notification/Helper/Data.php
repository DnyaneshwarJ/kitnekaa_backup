<?php
class Kitnekaa_Notification_Helper_Data extends Mage_Core_Helper_Data{

	
	
   /**
     * Retrieve notification list page url
     *
     * @return string
     */
    public function getNotificationsUrl()
    {
        return $this->_getUrl('notification/list');
    }
    
}