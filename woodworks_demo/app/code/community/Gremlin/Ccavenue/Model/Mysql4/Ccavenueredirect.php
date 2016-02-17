<?php
/**
 * Ccavenue MySQL4 Redirect Model
 *
 * @category    Model
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_Model_Mysql4_Ccavenueredirect extends Mage_Core_Model_Mysql4_Abstract {
	protected function _construct() {
		$this->_init( 'ccavenue/ccavenueredirect', 'redirect_id' );
	}
}
