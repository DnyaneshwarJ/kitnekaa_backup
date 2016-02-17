<?php

class Bobcares_Quote2Sales_Model_Request extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('quote2sales/request');
    }

    public function getAllRequests($customerId) {

        //$customer = Mage::getModel('customer/customer');
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('quote2sales_read');
        try {
            if (!$customerId)
                throw new Exception("Customer ID empty");
            $requestsTable = $resource->getTableName('quote2sales_requests');

            $select = $read->select()
                    ->from($requestsTable, array('request_id', 'comment'))
                    ->where('customer_id=' . $customerId)
                    ->order('request_id DESC');
            return $read->fetchAll($select);
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $read->rollBack();
            throw $e;
        }
        return array();
    }

//    //save and load methods
//    public function save() {
//        $resource = Mage::getSingleton('core/resource');
//        $connection = $resource->getConnection('quote2sales_write');
//        $read = $resource->getConnection('quote2sales_read');
//        $connection->beginTransaction();
//        $requestsTable = $resource->getTableName('quote2sales_requests');
//        try {
//            $this->_beforeSave();
//            $query = 'insert into ' . $requestsTable . '(customer_id, comment, name, email, phone, status) VALUES('
//                    . $this->getCustomer_id() . ","
//                    . "'" . $this->getComment() . "',"
//                    . "'" . $this->getName() . "',"
//                    . "'" . $this->getEmail() . "',"
//                    . "'" . $this->getPhone() . "',"
//                    . "'Waiting')";
//
//            echo $query;
//            $connection->query($query);
//
//            $connection->commit();
//            $this->_afterSave();
//        } catch (Exception $e) {
//            Mage::log('Exception:' . $e);
//            $connection->rollBack();
//            throw $e;
//        }
//        return $this;
//    }

    /**
     * @desc This fuction is used for updationg the status of the request
     * @param type $customerId : user id
     * @param type $status : status of the request
     * @return \Bobcares_Quote2Sales_Model_Request : query status
     * @throws Exception
     */
    public function updateRequestStatus($status, $requestId) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('quote2sales_write');
        $read = $resource->getConnection('quote2sales_read');
        $connection->beginTransaction();
        $requestsTable = $resource->getTableName('quote2sales_requests');

        try {
            $this->_beforeSave();
            $query = "update $requestsTable set status ='$status' where request_id=$requestId";
            $connection->query($query);
            $connection->commit();
            $this->_afterSave();
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @desc This fuction is used for inserting the quote and status of the request
     * @param type $status   : status of the request
     * @param type $requestId  : request id
     * @param type $quoteId  : quote id
     * @param type $orderId : order id
     * @return \Bobcares_Quote2Sales_Model_Request
     * @throws Exception
     */
    public function insertQuoteStatus($status, $requestId, $quoteId, $orderId) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('quote2sales_write');
        $read = $resource->getConnection('quote2sales_read');
        $requestsTable = $resource->getTableName('quote2sales_requests_status');
        $connection->beginTransaction();

        try {
            $this->_beforeSave();

            //If there the quote id is not null and order id is null then insert quote details else insert all data
            if ($quoteId != NULL && $orderId == NULL) {
                $query = "insert into $requestsTable (request_id, quote_id, status) VALUES($requestId,$quoteId," . "'$status'" . ")";
            } else {
                $query = "insert into $requestsTable (request_id, quote_id, status, order_id) VALUES($requestId,$quoteId," . "'$status'" . ",$orderId)";
            }
            $connection->query($query);
            $connection->commit();
            $this->_afterSave();
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @desc This function is used for deleting a request details from quote2sales_requests_status table
     * @param type $quoteId : quote id
     * @param type $requestId : request id
     * @return \Bobcares_Quote2Sales_Model_Request
     * @throws Exception
     */
    public function deleteQuoteStatus($quoteId = NULL, $requestId = NULL) {

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('quote2sales_write');
        $read = $resource->getConnection('quote2sales_read');
        $requestsTable = $resource->getTableName('quote2sales_requests_status');
        $connection->beginTransaction();

        try {
            $this->_beforeSave();

            //If there is no request id then delete status entry based on the quote id
            //If there is no quote id then delete status entry based on the request id
            if ($requestId == NULL) {
                $query = "delete from $requestsTable where quote_id=$quoteId";
            } else if ($quoteId == NULL) {
                $query = "delete from  $requestsTable where request_id=$requestId";
            }

            $connection->query($query);
            $connection->commit();
            $this->_afterSave();
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @desc This function is used for fetching the quote,status and order details of the request
     * @param type $requestId : request id
     * @return type : array of quote id, order id and status
     * @throws Exception
     */
    public function getRequestData($requestId) {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('quote2sales_read');
        try {

            //if there is no request id then throw an exception
            if (!$requestId) {
                throw new Exception("request ID empty");
            }

            $requestsTable = $resource->getTableName('quote2sales_requests_status');
            $select = $read->select()
                    ->from($requestsTable, array('quote_id', 'order_id', 'status'))
                    ->where('request_id=' . $requestId);
            return $read->fetchAll($select);
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $read->rollBack();
            throw $e;
        }
        return array();
    }

    /**
     * @desc Function fetch the customer corresponding to the request id
     * @param type $requestId : id of the request
     * @return type : return customer id corresponding to the input param
     * @throws Exception
     */
    public function getUserData($requestId) {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('quote2sales_read');
        try {

            //if there is no request id then throw an exception
            if (!$requestId) {
                throw new Exception("request ID empty");
            }
            $requestsTable = $resource->getTableName('quote2sales_requests');

            $select = $read->select()
                    ->from($requestsTable, array('customer_id', 'status'))
                    ->where('request_id=' . $requestId);
            return $read->fetchAll($select);
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $read->rollBack();
            throw $e;
        }
        return array();
    }

    /**
     *  @desc Function fetch the request id corresponding to the quote id
     * @param type $quoteId : quote id
     * @return type : request details
     * @throws Exception
     */
    public function getQuoteData($quoteId) {

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('quote2sales_read');
        try {

            //if there is no request id then throw an exception
            if (!$quoteId) {
                throw new Exception("quote ID empty");
            }
            $requestsTable = $resource->getTableName('quote2sales_requests_status');
            $select = $read->select()
                    ->from($requestsTable, array('request_id'))
                    ->where('quote_id=' . $quoteId);
            return $read->fetchAll($select);
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $read->rollBack();
            throw $e;
        }
        return array();
    }

    /**
     * @desc This fuction is used for updationg the status of the request, add order id in table
     * @param type $status : status of the request
     * @param type $quoteId : quote id
     * @param type $orderId : order id
     * @return \Bobcares_Quote2Sales_Model_Request  : query status
     * @throws Exception
     */
    public function addOrderId($status, $quoteId, $orderId) {

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('quote2sales_write');
        $read = $resource->getConnection('quote2sales_read');
        $requestsTable = $resource->getTableName('quote2sales_requests_status');
        $connection->beginTransaction();

        try {
            $this->_beforeSave();
            $query = "update $requestsTable set status ='$status', order_id=$orderId where quote_id=$quoteId";
            $connection->query($query);
            $connection->commit();
            $this->_afterSave();
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * @desc This function is used for fetching all requests in quote2sales_requests table
     * @return type : array of all requests
     * @throws Exception
     */
    public function getAllData() {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('quote2sales_read');
        try {
            $requestsTable = $resource->getTableName('quote2sales_requests');
            $select = $read->select()
                    ->from($requestsTable, array('request_id', 'quote_id', 'order_id', 'status'))
                    ->order('request_id DESC');
            return $read->fetchAll($select);
        } catch (Exception $e) {
            Mage::log('Exception:' . $e);
            $read->rollBack();
            throw $e;
        }
        return array();
    }

//    /**
//     * @desc This fuction is used for updationg the seller comment of the request.
//     * @param type $requestId : request id
//     * @param type $sellerComment : seller comment
//     * @throws Exception
//     */
//    public function updateSellerComment($requestId, $sellerComment) {
//        $resource = Mage::getSingleton('core/resource');
//        $connection = $resource->getConnection('quote2sales_write');
//        $connection->beginTransaction();
//        $requestsTable = $resource->getTableName('quote2sales_requests');
//
//        try {
//            $this->_beforeSave();
//            $query = "update $requestsTable set seller_comment ='$sellerComment' where request_id=$requestId";
//            print_r($query);
//
//            $connection->query($query);
//            $connection->commit();
//            $this->_afterSave();
//        } catch (Exception $e) {
//            Mage::log('Exception:' . $e);
//            $connection->rollBack();
//            throw $e;
//        }
//        return $this;
//    }
//
//    /**
//     * @desc This function is used for fetch the seller comment in quote2sales_requests table
//     * @return type : array of seller comment
//     * @throws Exception
//     */
//    public function getSellerComment($quoteId) {
//        $resource = Mage::getSingleton('core/resource');
//        $read = $resource->getConnection('quote2sales_read');
//        try {
//            $requestsTable = $resource->getTableName('quote2sales_requests');
//            $select = $read->select()
//                    ->from($requestsTable, array('seller_comment'))
//                    ->where("request_id = (select request_id from quote2sales_requests_status where quote_id = " . $quoteId . ")");
//            return $read->fetchAll($select);
//        } catch (Exception $e) {
//            Mage::log('Exception:' . $e);
//            $read->rollBack();
//            throw $e;
//        }
//        return array();
//    }
}
