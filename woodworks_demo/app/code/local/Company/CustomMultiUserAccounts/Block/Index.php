<?php
	class Company_CustomMultiUserAccounts_Block_Index extends Mage_Core_Block_Template{

		public function getCustomer(){
			//return Mage::getSingleton('customer/session')->getCustomer();
			return Mage::getSingleton('customer/session');
		}
	}
?>