<?php
class Bobcares_Quote2Sales_Block_Customer_Login extends Mage_Customer_Block_Form_Login{
	/**
	 * Retrieve form posting url
	 *
	 * @return string
	 */
	public function getPostActionUrl()
	{
	//	return $this->helper('customer')->getLoginPostUrl();
		return Mage::registry("loginpost_url"); 
	}
	public function setPostActionUrl($url){
		Mage::register('loginpost_url', $url, true);
	
	}
}