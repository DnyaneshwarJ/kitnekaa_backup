<?php

/**
 * @created on 6th November 2015
 * @author Dnyaneshwar
 *
 * This is the resource class for the quote2sales_requests_products  table
 */
class Kitnekaa_Quote2SalesCustom_Model_Mysql4_Requestproducts extends Mage_Core_Model_Mysql4_Abstract {

    /* constructor to intialize the requestproducts resource class */
    public function _construct() {
        $this->_init('quote2salescustom/requestproducts', 'id'); // id refers to the primary key of the request_products table
    }

}
