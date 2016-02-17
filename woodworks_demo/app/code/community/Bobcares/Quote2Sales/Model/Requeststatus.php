<?php

/**
 * @created on 18th May 2015
 * @author Bobcares
 * 
 * This is the model class for the quote2sales_requests_status  table
 */
class Bobcares_Quote2Sales_Model_Requeststatus extends Mage_Core_Model_Abstract {

    /* constructor to intialize the requeststatus model class */
    public function _construct() {
        parent::_construct();
        $this->_init('quote2sales/requeststatus');
    }

}
