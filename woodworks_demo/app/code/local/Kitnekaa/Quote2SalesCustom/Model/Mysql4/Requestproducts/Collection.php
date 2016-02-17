<?php

/**
 * @created on 6th November 2015
 * @author Dnyaneshwar
 * 
 * This is the collection class for the quote2sales_requests_products  table
 */
class Kitnekaa_Quote2SalesCustom_Model_Mysql4_Requestproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
  
    /* constructor to intialize the requestproducts resource class */
    public function _construct() {
        parent::_construct();
        $this->_init('quote2salescustom/requestproducts');
    }

}
