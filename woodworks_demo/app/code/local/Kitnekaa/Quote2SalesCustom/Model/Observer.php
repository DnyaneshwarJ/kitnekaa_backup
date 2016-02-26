<?php

class Kitnekaa_Quote2SalesCustom_Model_Observer extends Mage_Core_Model_Abstract
{
    function quote2sales_index_post()
    {
        $request_quote = Mage::app()->getRequest()->getPost('request_quote');
        $request_quote_products = Mage::app()->getRequest()->getPost('shopp_list_items');
        $post = Mage::app()->getRequest()->getPost();
        if ($post) {
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);
                $error = FALSE;

                $msg = "Unable to submit your request. Please, try again later";


                foreach ($request_quote_products['product_id'] as $k => $qproduct_id) {
                    if (!Zend_Validate::is(trim($request_quote_products['qty'][$k]), 'NotEmpty')) {
                        $msg = "Please enter Quantity!";
                        $error = TRUE;
                        goto error;
                    }
                    if (!Zend_Validate::is(trim($request_quote_products['target_price'][$k]), 'NotEmpty')) {
                        $msg = "Please enter Target Price!";
                        $error = TRUE;
                        goto error;
                    }
                    if (!Zend_Validate::is(trim($request_quote_products['comment'][$k]), 'NotEmpty')) {
                        $msg = "Please enter Comment!";
                        $error = TRUE;
                        goto error;
                    }
                }

              /*  if (!Zend_Validate::is(trim($request_quote['billing_address_id'][0]), 'NotEmpty')) {
                    $msg = "Please select Billing Address!";
                    $error = TRUE;
                    goto error;
                }

                if (!Zend_Validate::is(trim($request_quote['delivery_location'][0]), 'NotEmpty')) {
                    $msg = "Please select Delivery Location!";
                    $error = TRUE;
                    goto error;
                }*/

                error:
                {
                    if ($error) {
                        throw new Exception();
                    }
                }
                //If the user is logged in then save request and display success message.
                //Else display an error message
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $this->saveRequest();
                } else {

                    Mage::getSingleton('core/session')->addError(Mage::helper('quote2sales')->__('You are not logged in.'));
                }

                $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();

                //return;
            } catch (Exception $e) {
                //echo "aaa";
                //exit();

                //$translate->setTranslateInline(true);
                Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__($msg));
                $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
                //return;
            }
        } else {
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
        }

        Mage::app()->getResponse()->setRedirect($url);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    function saveRequest()
    {
        $data = Mage::app()->getRequest()->getPost();
        $data['upload_files']=TRUE;

        try {
            $request_model=Mage::getModel('quote2sales/request');
            $request_model->setData($data)->save();
            $request_model->sendEmail();
            /* If data not saved correctly */
            $viewRequestsURL = Mage::getUrl('quote2sales/request/');
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote2sales')->__('Your request was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('quote2sales')->__('Request for Quote was successfully saved. <a href="' . $viewRequestsURL . '">View all saved requests</a>'));

        } catch (Mage_Core_Exception $e) {
            print_r($e);
            Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__('Unable to submit your request. Please, try again later'));
        }

        $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer() : Mage::getUrl();
        Mage::app()->getResponse()->setRedirect($url);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    function setCustomDataOnQuoteSave($observer)
    {


        $quote = $observer->getEvent()->getQuote();
        $quote_session = Mage::getSingleton('adminhtml/session_quote');

        $request = Mage::getModel('quote2sales/request')->load($quote_session->getRequestId());
        $customer_id = $quote_session->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $login_user = Mage::getSingleton('admin/session')->getUser();

        //$customer->setDefaultBilling($request->getBillingAddressId())->setId($customer_id)->save();
        //Mage::getModel('customer/address')->setIsDefaultBilling('1')->setId($request->getBillingAddressId())->save();
        //Mage::getModel('customer/address')->setIsDefaultShipping('1')->setId($request->getDeliverylocation())->save();
        $address = Mage::getModel('customer/address')->load($request->getDeliverylocation());
        $quote->setShippingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($address));
        if ($quote_session->getRequestId()) {
            $quote->setCompanyId($request->getCompanyId());
            $quote->setCompanyName(Mage::helper('quote2sales')->getCompanyById($request->getCompanyId())->getCompanyName());
            $quote->setQuoteRequestBy($request->getName());
        } else {
            //Mage::helper('users')->printPre($customer->getData());
            $quote->setCompanyId($customer->getCompanyId());
            $quote->setCompanyName(Mage::helper('quote2sales')->getCompanyById($customer->getCompanyId())->getCompanyName());
            $quote->setQuoteRequestBy('-');
        }
//die;
        $quote->setQuoteBy(Mage::helper('users')->getUserFullName($login_user));
        return;
    }


}