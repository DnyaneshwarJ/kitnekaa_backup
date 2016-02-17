<?php
class Bobcares_Quote2Sales_Model_Observer{
	
	public function customerLogin(Varien_Event_Observer $observer)
	{		
//		if (Mage::helper('quote2sales')->isEnabled()) {
	//		$_session = $this->_getSession();
	//		$_session->setBeforeAuthUrl(Mage::getUrl('*/request/post', array('comments'=>"this is a test")));
//		}
	}
	/**
	 * @return Mage_Core_Model_Abstract
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}
}