<?php

require_once 'Mage/Customer/controllers/AccountController.php';

class Bobcares_Quote2Sales_IndexController extends Mage_Core_Controller_Front_Action {
    
    const XML_PATH_ENABLED = 'quotes/quotes/enabled';

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }

    /*
     * Gets the session
     * @return Mage_Customer_Model_Session
     */

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function indexAction() {

        /* If request has quote Id */
        if ($_GET['rejectquoteid']) {
            $this->rejectQuote($_GET['rejectquoteid']);
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('requestForm')
                ->setFormAction(Mage::getUrl('*/*/post'));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function postAction() {
        $post = $this->getRequest()->getPost();
        if ($post) {
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);
               
                Mage::log($postObject);
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $error = false;

                if (!Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['deliverylocation']), 'NotEmpty')) {
                    $error = true;
                }

              

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

          

              

                if ($error) {

                    throw new Exception();
                }

 
                //If the user is logged in then save request and display success message.
                //Else display an error message
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    
                    $this->_forward($action = 'saveRequest', $controller = 'request', $module = 'quote2sales', $params = array('comment' => $postObject->getComment()));



                } else {
                     
                    Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__('You are not logged in.'));
                }
                 
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                echo "aaa";
                exit();

                $translate->setTranslateInline(true);
                Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * @desc Rejects current quote and redirects to RFQ page
     * @param $quoteId quote Id to reject
     */
    public function rejectQuote($quoteId) {

        $requestId = Mage::getModel('quote2sales/requeststatus')->getCollection()
                        ->addFieldToSelect('request_id')
                        ->addFieldToFilter('quote_id', ((int) $quoteId))->getFirstItem()->getData('request_id');

        /* If request Id exists */
        if ($requestId) {
            $statusTable = Mage::getModel('quote2sales/requeststatus')->getCollection()
                            ->addFieldToFilter('quote_id', ((int) $quoteId))->getFirstItem();
            $statusTable->setData('status', 'Rejected');
            $statusTable->save();
            $requestTable = Mage::getModel('quote2sales/request')->getCollection()
                            ->addFieldToFilter('request_id', ((int) $requestId))->getFirstItem();
            $requestTable->setData('status', 'Rejected');
            $requestTable->save();
        }
    }

}
