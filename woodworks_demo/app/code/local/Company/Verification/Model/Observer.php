<?php

class Company_Verification_Model_Observer extends Mage_Core_Model_Abstract
{

    /* Call after customer login */
    public function customerLogin($observer)
    {

        $customer = $observer->getEvent()->getCustomer();

        $is_mobile_confirmed = $customer->getMobNoVerification();

        $session = Mage::getSingleton('customer/session');
        $response = Mage::app()->getFrontController()->getResponse();

        /* Checking Mobile number verified or not after login */
        if (Mage::getStoreConfig('customer/create_account/verify_mobile', Mage::app()->getStore())) {
            if ($is_mobile_confirmed == 0) {

                Mage::getSingleton('core/session')->setKitCustomerId($customer->getId());
                Mage::getSingleton('core/session')->setOTPTime(strtotime(now()));
                $session->setId(NULL)
                    ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
                    ->getCookie()->delete('customer');

                $url = Mage::getUrl('verification/index/index/');
                $response->setRedirect($url);
                $response->sendResponse();
                exit;
            }
            //Mage::throwException(__('This account is not activated.'));
            //return;
        }
    }


    /* Call on customer registration done successfully */
    public function  registrationSuccess($observer)
    {
      /*  if (Mage::getStoreConfig('customer/create_account/verify_mobile', Mage::app()->getStore())) {
            $event = $observer->getEvent();
            $customer = $event->getCustomer();
            $otp_value = Mage::helper('verification')->sendOTP($customer->getPhoneno());
            $customer->setOtpText($otp_value);
            $customer->getResource()->saveAttribute($customer, 'otp_text');

            Mage::getSingleton('core/session')->addSuccess('A one time password(OTP) was sent to the registed phone number. Please use this to verify your account.');
        }*/
    }
}