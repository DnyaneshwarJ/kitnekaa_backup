<?php

/**
 * @created on 18th May 2015
 * @author Bobcares
 * 
 * This is the resource class for the quote2sales_requests_status  table
 */
class Bobcares_Quote2Sales_Model_Mysql4_Requeststatus extends Mage_Core_Model_Mysql4_Abstract {
    
    /* constructor to intialize the requeststatus resource class */
    public function _construct() {
        $this->_init('quote2sales/requeststatus', 'status_id'); // id refers to the primary key of the request_status table
    }

}
