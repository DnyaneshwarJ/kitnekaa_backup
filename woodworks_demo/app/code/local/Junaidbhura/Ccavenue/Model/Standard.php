<?php
/**
 * Main payment model
 *
 * @category    Model
 * @package     Junaidbhura_Ccavenue
 * @author      Junaid Bhura <info@junaidbhura.com>
 */

class Junaidbhura_Ccavenue_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'ccavenue';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl( 'ccavenue/payment/redirect', array( '_secure' => true ) );
	}
}
?>