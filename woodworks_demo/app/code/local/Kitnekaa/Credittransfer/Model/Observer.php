<?php
/**
 * Magento
 *
 *
 * @category    Sharpdot
 * @package     Sharpdot_SharpAdminOnlyPayment
 * @copyright   Copyright (c) 2010 Sharpdot Inc. (http://www.sharpdotinc.com)
 * @license     
 */

/**
 *
 * @category   Sharpdot
 * @package    Sharpdot_SharpAdminOnlyPayment
 * @author     Michael Dost <mdost@sharpdotinc.com>
 */
class Kitnekaa_Credittransfer_Model_Observer
{
	public function checkAdminPaymentAllowed($observer)
	{
		
		if(!$observer->event->result->isAvailable){
			$code = $observer->event->method_instance->getCode(); 
			$prefix = 'payment/'.$code.'/';	
			
	    	if (Mage::getStoreConfigFlag($prefix.'admin_active')) {
	    		//change to allow it
	    		$observer->event->result->isAvailable = 1;	                
	        }
		}
		
	}
}