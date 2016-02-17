<?php
/**
 * @author CreativeMindsSolutions
 */

require_once('Cminds/MultiUserAccounts/controllers/AccountController.php');

class Company_CustomMultiUserAccounts_AccountController extends Cminds_MultiUserAccounts_AccountController
{
    /**
     * Default customer account page
     */
    public function subAccountAction()
    {

        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $this->getLayout()->getBlock('head')->setTitle($this->__('Manage Users'));
            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    public function addSubAccountAction()
    {

        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $this->getLayout()->getBlock('head')->setTitle($this->__('Add User'));
            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    public function editSubAccountAction()
    {

        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $subAccountId = $this->getRequest()->getParam('id');
            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccountId);

            if ($subAccount->getId() && $this->_canViewSubAccount($subAccount)) {

                $data = $this->_getSession()->getSubUserFormData(TRUE);
                if (!empty($data)) {
                    $subAccount->addData($data);
                }

                $block = $this->getLayout()->getBlock('edit_subaccount');
                if ($block) {
                    $block->setRefererUrl($this->_getRefererUrl());
                }

                if ($this->getRequest()->getParam('changepass') == 1) {
                    $subAccount->setChangePassword(1);
                }
                $block->setSubAccount($subAccount);
            } else {
                $this->_getSession()->addError('Invalid User');

                return $this->_redirect('*/*/subAccount');
            }

            $this->getLayout()->getBlock('head')->setTitle($this->__('Account Information'));
            $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(TRUE);
            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }


    /**
     * Create customer account action
     */
    public function addSubAccountPostAction()
    {
        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            /** @var $session Mage_Customer_Model_Session */
            $session = $this->_getSession();

            $session->setEscapeMessages(TRUE); // prevent XSS injection in user input
            if (!$this->getRequest()->isPost()) {
                $errUrl = $this->_getUrl('*/*/subAccount', array('_secure' => TRUE));
                $this->_redirectError($errUrl);

                return;
            }

            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->setId(NULL);

            try {
                $data = $this->getRequest()->getParams();
                $data['password'] = $this->generateRandomString();
                Mage::log($data,null,'password.log');
                $data['parent_customer_id'] = $this->_getSession()->getCustomer()->getId();
                $data['store_id'] = $this->_getSession()->getCustomer()->getStoreId();
                $data['website_id'] = $this->_getSession()->getCustomer()->getWebsiteId();
                $subAccount->addData($data);

                if ($errors = $subAccount->validate()) {
                    // set hash pass word
                    $subAccount->setPassword($data['password']);
                    $subAccount->save();
                    $this->_successProcessSubAccountRegistration($subAccount);

                    return;
                } else {
                    $this->_addSessionError($errors);
                }
            } catch (Mage_Core_Exception $e) {
                $session->setSubUserFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = $this->_getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(FALSE);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setSubUserFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the user.'));
            }
            $errUrl = $this->_getUrl('*/*/addSubAccount', array('_secure' => TRUE));
            $this->_redirectError($errUrl);
        } else {
            $this->_forward('noRoute');
        }

    }

    public function editSubAccountPostAction()
    {
        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            if (!$this->_validateFormKey()) {
                return $this->_redirect('*/*/editSubAccount', array('id' => $this->getRequest()->getParam('id')));
            }

            $subAccountId = $this->getRequest()->getParam('id');
            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccountId);

            if ($this->getRequest()->isPost() && $this->_canViewSubAccount($subAccount)) {
                $data = $this->getRequest()->getParams();

                if ($data) {
                    $errors = array();

                    // If password change was requested then add it to common validation scheme
                    if ($this->getRequest()->getParam('change_password')) {
                        $currPass = $this->getRequest()->getPost('current_password');
                        $newPass = $this->getRequest()->getPost('password');
                        $confPass = $this->getRequest()->getPost('confirmation');

                        $oldPass = $subAccount->getPasswordHash();

                        if ($this->_getHelper('core/string')->strpos($oldPass, ':')) {
                            list($_salt, $salt) = explode(':', $oldPass);
                        } else {
                            $salt = FALSE;
                        }

                        if ($subAccount->hashPassword($currPass, $salt) == $oldPass) {
                            if (strlen($newPass)) {
                                /**
                                 * Set entered password and its confirmation - they
                                 * will be validated later to match each other and be of right length
                                 */
                                $subAccount->setPassword($newPass);
                                $subAccount->setConfirmation($confPass);
                            } else {
                                $errors[] = $this->__('New password field cannot be empty.');
                            }
                        } else {
                            $errors[] = $this->__('Invalid current password');
                        }

                    } else { // no change password
                        if (isset($data['confirmation'])) {
                            unset($data['confirmation']);
                        }
                        if (isset($data['password'])) {
                            unset($data['password']);
                        }
                        if (isset($data['current_password'])) {
                            unset($data['current_password']);
                        }
                    }

                    $subAccount->addData($data);
                    // Validate account and compose list of errors if any
                    $subAccountErrors = $subAccount->validate();
                    if (is_array($subAccountErrors)) {
                        $errors = array_merge($errors, $subAccountErrors);
                    }

                    if (!empty($errors)) {
                        $this->_getSession()->setSubUserFormData($this->getRequest()->getPost());
                        foreach ($errors as $message) {
                            $this->_getSession()->addError($message);
                        }
                        $this->_redirect('*/*/editSubAccount', array('id' => $subAccount->getId()));

                        return $this;
                    }

                } else {
                    $this->_getSession()->setSubUserFormData($this->getRequest()->getPost());
                    $this->_getSession()->addError('Missing Data');
                    $this->_redirect('*/*/editSubAccount', array('id' => $subAccount->getId()));

                    return $this;
                }

                try {
                    //                $subAccount->setConfirmation(null);
                    $subAccount->save();
                    $this->_getSession()->addSuccess($this->__('User information has been saved.'));

                    $this->_redirect('customer/account/subAccount');

                    return;
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->setSubUserFormData($this->getRequest()->getPost())
                        ->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->setSubUserFormData($this->getRequest()->getPost())
                        ->addException($e, $this->__('Cannot save the user.'));
                }
            }

            $this->_redirect('*/*/editSubAccount');
        } else {
            $this->_forward('noRoute');
        }

    }

    public function deleteSubAccountAction()
    {
        if (!Mage::getSingleton('customer/session')->getSubAccount() || Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin()) {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $subAccountId = $this->getRequest()->getParam('id');
            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccountId);

            try {
                if ($subAccount->getId() && $this->_canViewSubAccount($subAccount)) {
                    $email = $subAccount->getEmail();
                    $subAccount->delete();
                    $this->_getSession()->addSuccess($this->__('User %s has been deleted.', $email));

                } else {
                    $this->_getSession()->addError('Invalid User');
                }
            } catch (Exception $e) {
                $this->_getSession()->addError('An Error occurred');
            }

            $this->getLayout()->getBlock('head')->setTitle($this->__('Account Information'));
            $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(TRUE);
            $this->renderLayout();

            return $this->_redirect('*/*/subAccount');
        } else {
            $this->_forward('noRoute');
        }
    }


    public function loginPostAction()
    {

        // generate form_key if missing or invalid
        if (!($formKey = $this->getRequest()->getParam('form_key', null)) || $formKey != Mage::getSingleton('core/session')->getFormKey()) {
            $this->getRequest()->setParams(array('form_key' =>Mage::getSingleton('core/session')->getFormKey()));
        }

        $is_ajax = Mage::app()->getRequest()->getPost('is_ajax')?Mage::app()->getRequest()->getPost('is_ajax'):FALSE;
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');

            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');

            return;
        }
        $session = $this->_getSession();
        if ($this->getRequest()->isXmlHttpRequest()) {
            // Report exceptions via JSON
            $ajaxExceptions = "";
            $isAjaxExceptions = FALSE;
        }

        if ($this->getRequest()->isPost()) {

            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), TRUE);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            $ajaxExceptions = $message;
                            $isAjaxExceptions = TRUE;
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            $ajaxExceptions = $message;
                            $isAjaxExceptions = TRUE;
                            break;
                        default:
                            $message = $e->getMessage();
                            $ajaxExceptions = $message;
                            $isAjaxExceptions = TRUE;
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                    $message = $e->getMessage();
                    $ajaxExceptions = $message;
                    $isAjaxExceptions = TRUE;
                }
            } else {
                $ajaxExceptions = $this->__('Login and password are required.');
                $session->addError($this->__('Login and password are required.'));
                $isAjaxExceptions = TRUE;
            }
        }

        if ($is_ajax) {
            if ($isAjaxExceptions) {
                $result = array('success' => FALSE, 'msg' => $ajaxExceptions);
                echo json_encode($result);
                exit;
            } else {

                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $result = array('success' => TRUE, 'msg' => 'You have login successfully!');
                    echo json_encode($result);
                }
                else
                {
                    $result = array('success'=>FALSE,'msg'=>'You are not logged in!');
                    echo json_encode($result);
                }
                exit;
            }

        } else {

            $this->_loginPostRedirect();
        }

    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();
            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('An email was sent to the registered email id. Please check your email and follow the specified instructions. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));
        } else {
            $session->setCustomerAsLoggedIn($customer);
            $url = $this->_welcomeCustomer($customer);
        }
        $this->_redirectSuccess($url);
        return $this;
    }

    /**
     * Create a random string for password
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /** sub customer account information page */
    public function editAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            return parent::editAction();
        }
        Mage::getSingleton('customer/session')->addError('You Don\'t have permission for this action');
        if (count($this->_getSession()->getCustomer()->getAddresses())) {
            return $this->_redirect('*/*/');
        }else{
            return $this->_redirect('customer/account/');
        }
    }

    /**
     * Confirm customer account by id and confirmation key
     */
    public function confirmAction()
    {
        $session = $this->_getSession();
        $subAccountMode = false;
        if ($session->isLoggedIn()) {
            $this->_getSession()->logout()->regenerateSessionId();
        }
        try {
            $id = $this->getRequest()->getParam('id', false);
            $key = $this->getRequest()->getParam('key', false);
            $backUrl = $this->getRequest()->getParam('back_url', false);
            if (empty($id) || empty($key)) {
                throw new Exception($this->__('Bad request.'));
            }

            // load customer by id (try/catch in case if it throws exceptions)
            try {

                if (strpos($key, Cminds_MultiUserAccounts_Model_SubAccount::KEY_SIGN) === FALSE) {
                    $account = $this->_getModel('customer/customer')->load($id);
                } else {
                    $account = $this->_getModel('cminds_multiuseraccounts/subAccount')->load($id);
                    $subAccountMode = true;
                }

            } catch (Exception $e) {
                throw new Exception($this->__('Wrong customer account specified.'));
            }

            // check if it is inactive
            if ($account->getConfirmation()) {
                if ($account->getConfirmation() !== $key) {
                    throw new Exception($this->__('Wrong confirmation key.'));
                }

                // activate customer
                try {
                    $account->setConfirmation(null);
                    $account->save();
                } catch (Exception $e) {
                    throw new Exception($this->__('Failed to confirm customer account.'));
                }

                $session->renewSession();
                if (!$subAccountMode) {

                    //start OTP send to new customer
                    if (Mage::getStoreConfig('customer/create_account/verify_mobile', Mage::app()->getStore())) {
                        $otp_value = Mage::helper('verification')->sendOTP($account->getPhoneno());
                        $account->setOtpText($otp_value);
                        $account->getResource()->saveAttribute($account, 'otp_text');
                        Mage::getSingleton('core/session')->addSuccess('A one time password(OTP) was sent to the registered phone number. Please use this to verify your account.');
                    }
                    //end otp

                    $session->setCustomerAsLoggedIn($account);
                } else {
                    $customer = $this->_getModel('customer/customer')->load($account->getParentCustomerId());
                    $session->setCustomerAsLoggedIn($customer);
                    $session->setSubAccount($account);
                }

                // log in and send greeting email, then die happy
                $successUrl = $this->_welcomeCustomer($account, true);
                $this->_redirectSuccess($backUrl ? $backUrl : $successUrl);
                return;
            }

            // die happy
            $this->_redirectSuccess($this->_getUrl('*/*/index', array('_secure' => true)));
            return;
        } catch (Exception $e) {
            // die unhappy
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectError($this->_getUrl('*/*/index', array('_secure' => true)));
            return;
        }
    }
}