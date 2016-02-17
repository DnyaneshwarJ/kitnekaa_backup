<?php
/**
 * Ccavenue MySQL4 Redirect Collection Model
 *
 * @category    Model
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_Model_Mysql4_Ccavenueredirect_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct(){
		$this->_init( 'ccavenue/ccavenueredirect' );
	}
}
