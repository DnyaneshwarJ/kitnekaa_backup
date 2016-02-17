<?php

class Company_Verification_IndexController extends Mage_Core_Controller_Front_Action
{

    /* OTP verification page */
    public function IndexAction()
    {

        if (!Mage::getSingleton('core/session')->getKitCustomerId()) {
            $this->_forward('noRoute');
        } else {
            $this->loadLayout();
            $this->renderLayout();
        }

    }

    public function VerifynoAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        $otp = $this->getRequest()->getPost('otp');
        $phoneno = $this->getRequest()->getPost('phoneno');
        $newmob = $this->getRequest()->getPost('newmob');

        $email = $this->getRequest()->getPost('email');
        //var_dump($email);die;
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);

        if ($newmob) {
            $otp_value = Mage::helper('verification')->sendOTP($phoneno);
            $customer->setPhoneno($phoneno);
            $customer->getResource()->saveAttribute($customer, 'phoneno');
            $customer->setOtpText($otp_value);
            $customer->getResource()->saveAttribute($customer, 'otp_text');
            //$customer->save();
            Mage::getSingleton('core/session')->addSuccess('OTP Sent To your new mobile number successfully!');
            $this->_redirectReferer();
            //exit;
        } else {
            $verifyno = $customer->getOtpText();
            $now = time();
            $difference = $now - Mage::getSingleton('core/session')->getOTPTime();
            $minutes = floor($difference / 60);

            if ($minutes > 15) {
                $customer->setOtpText(mt_rand(100000, 999999));
                $customer->getResource()->saveAttribute($customer, 'otp_text');
                Mage::getSingleton('core/session')->addError('OTP is expired resend it!');
                $this->_redirectReferer();
            } else {

                if ($verifyno == $otp) {
                    $customer->setMobNoVerification(1);
                    $customer->getResource()->saveAttribute($customer, 'mob_no_verification');
                    //$customer->save();
                    //$customer->getResource()->saveAttribute($customer, 'mobile_no_verification');
                    Mage::getSingleton('core/session')->unsKitCustomerId();
                    Mage::getSingleton('core/session')->unsOTPTime();
                    Mage::getSingleton('core/session')->addSuccess('Mobile number Successfully Verified. Login With your credentials.');
                    $this->_redirect('customer/account/login');
                } else {
                    Mage::getSingleton('core/session')->addError('Not valid mobile number or OTP is invalid!');
                    $this->_redirectReferer();
                }
            }

        }

    }

    public function ResendotpAction()
    {
        $this->loadLayout();
        $this->renderLayout();
        $email = $this->getRequest()->getParam('q');
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);
        $mobileNumber = $customer->getPhoneno();
        $otp_value = Mage::helper('verification')->sendOTP($mobileNumber);
        $customer->setOtpText($otp_value);
        $customer->getResource()->saveAttribute($customer, 'otp_text');
        // $customer->save();
        Mage::getSingleton('core/session')->setOTPTime(strtotime(now()));
        Mage::getSingleton('core/session')->addSuccess('New OTP Sent successfully!');
        $this->_redirectReferer();
    }

    public function ChangepassnewAction()
    {

        $this->loadLayout();
        $this->renderLayout();
        $passwordnew = $this->getRequest()->getPost('currentpassnew');
        $statuschange = 376;
        $email = $this->getRequest()->getPost('email');
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);
        $customer->setPassword($passwordnew);
        $customer->setPasswordstatus($statuschange);
        $customer->save();
        Mage::getSingleton('core/session')->addSuccess('Successfully Changed Password');
        $redirection_url = Mage::getBaseUrl() . "customer/account/login/";
        $this->_redirectUrl($redirection_url);
    }


    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     */
    protected function _forward($action, $controller = NULL, $module = NULL, array $params = NULL)
    {
        $request = $this->getRequest();

        $request->initForward();

        if (isset($params)) {
            $request->setParams($params);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)
            ->setDispatched(FALSE);
    }
}