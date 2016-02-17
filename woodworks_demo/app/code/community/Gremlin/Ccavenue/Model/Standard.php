<?php
/**
 * Main payment Model
 *
 * @category    Model
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	/* Variables */
	protected $_code = 'ccavenue';
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	/**
	 * Gets the URL after the order is placed
	 * 
	 * @return string
	 */
	public function getOrderPlaceRedirectUrl() {
		if ( Mage::getStoreConfig( 'payment/ccavenue/integration_method' ) == 'iframe' )
			return Mage::getUrl( 'ccavenue/payment/details', array( '_secure' => true ) );
		else
			return Mage::getUrl( 'ccavenue/payment/redirect', array( '_secure' => true ) );
	}

}
