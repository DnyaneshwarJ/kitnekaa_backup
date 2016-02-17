<?php
class Kitnekaa_Customertab_Model_Observer extends Varien_Event_Observer {
	public function approveCustomer(Varien_Event_Observer $observer) {
		/**
		 *
		 * @var $customer Mage_Customer_Model_Customer
		 */
		$request = Mage::app ()->getRequest ();
		$customer = $observer->getEvent ()->getCustomer ();
		$authDetails = $request->getPost ( 'auth' );
		
		if ($authDetails ['is_active']) {
			$customer->setIsActive ( $authDetails ['is_active'] );
		}else{
			$customer->setIsActive (0);
		}
		if ($authDetails ['participant_id']) 
		{
			$customer->setCompanyId ( $authDetails ['participant_id'] );
		}
	}
	
	public function getCustomerData(Varien_Event_Observer $observer)
	{
		$customer = $observer->getEvent ()->getCustomer ();
	}
}
?>