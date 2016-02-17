<?php

/**
 * @created on 18th May 2015
 * @author Bobcares
 * 
 * This is the collection class for the quote2sales_requests_status  table
 */
class Bobcares_Quote2Sales_Model_Mysql4_Requeststatus_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
  
    /* constructor to intialize the requeststatus resource class */
    public function _construct() {
        parent::_construct();
        $this->_init('quote2sales/requeststatus');
    }

}
