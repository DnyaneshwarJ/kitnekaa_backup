<?php
/**
 * @author CreativeMindsSolutions
 */

require_once 'Mage/Customer/controllers/AccountController.php';

class Cminds_MultiUserAccounts_AccountController extends Mage_Customer_AccountController
{
    /**
     * Default customer account page
     */
    public function subAccountAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $this->getLayout()->getBlock('head')->setTitle($this->__('Manage Users'));
            $this->renderLayout();
        }
    }

    public function addSubAccountAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $this->getLayout()->getBlock('head')->setTitle($this->__('Add User'));
            $this->renderLayout();
        }
    }

    public function editSubAccountAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');

            $subAccountId = $this->getRequest()->getParam('id');
            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccountId);

            if ($subAccount->getId() && $this->_canViewSubAccount($subAccount)) {

                $data = $this->_getSession()->getSubUserFormData(true);
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
            $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
            $this->renderLayout();
        }
    }

    public function deleteSubAccountAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
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
            $this->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
            $this->renderLayout();
            return $this->_redirect('*/*/subAccount');
        }
    }

    public function editSubAccountPostAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
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
                            $salt = false;
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
        }
    }

    /**
     * Create customer account action
     */
    public function addSubAccountPostAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount()){
            $this->_forward('noRoute');
        }else{
            /** @var $session Mage_Customer_Model_Session */
            $session = $this->_getSession();

            $session->setEscapeMessages(true); // prevent XSS injection in user input
            if (!$this->getRequest()->isPost()) {
                $errUrl = $this->_getUrl('*/*/subAccount', array('_secure' => true));
                $this->_redirectError($errUrl);
                return;
            }

            $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->setId(null);

            try {
                $data = $this->getRequest()->getParams();
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
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setSubUserFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the user.'));
            }
            $errUrl = $this->_getUrl('*/*/addSubAccount', array('_secure' => true));
            $this->_redirectError($errUrl);
        }
    }

    /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     *
     * @TODO MAIL CONFIRMATION
     */
    protected function _successProcessSubAccountRegistration(Cminds_MultiUserAccounts_Model_SubAccount $subAccount)
    {
        $session = $this->_getSession();
        if ($subAccount->isConfirmationRequired()) {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store */
            $store = $app->getStore();
            $subAccount->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );
            $subAccountHelper = $this->_getHelper('cminds_multiuseraccounts');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $subAccountHelper->getEmailConfirmationUrl($subAccount->getEmail())));
        }
        $session->addSuccess($this->__('New user created'));
        $url = $this->_getUrl('*/*/subAccount', array('_secure' => true));
        $this->_redirectSuccess($url);
        return $this;
    }

    protected function _canViewSubAccount($subAccount)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if ($subAccount->getId() && $subAccount->getParentCustomerId() && ($subAccount->getParentCustomerId() == $customerId)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Send confirmation link to specified email
     */
    public function accountconfirmationAction()
    {
        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->loadByEmail($email);
                if (!$subAccount->getId()) {
                    throw new Exception('');
                }
                if (!$subAccount->getConfirmation()) {
                    $subAccount->sendNewAccountEmail('confirmation', '', Mage::app()->getStore()->getId());
                    $this->_getSession()->addSuccess($this->__('Please, check your email for confirmation key.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('This email does not require confirmation.'));
                }
                $this->_getSession()->setUsername($email);
                $this->_redirectSuccess($this->_getUrl('*/*/*', array('_secure' => true)));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Wrong email.'));
                $this->_redirectError($this->_getUrl('*/*/*', array('email' => $email, '_secure' => true)));
            }
            return;
        }

        // output form
        $this->loadLayout();

        $this->getLayout()->getBlock('accountConfirmation')
            ->setEmail($this->getRequest()->getParam('email', $email));

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if (Mage::helper('cminds_multiuseraccounts')->hasWritePermission()) {
            return parent::editPostAction();
        }
        $this->_getSession()->addError('You Don\'t have permission for this action');
        return $this->_redirect('*/*/');
    }

    /**
     * Send confirmation link to specified email
     */
    public function confirmationAction()
    {
        $customer = $this->_getModel('customer/customer');
        $subAccount = $this->_getModel('cminds_multiuseraccounts/subAccount');
//        if ($this->_getSession()->isLoggedIn()) {
//            $this->_redirect('*/*/');
//            return;
//        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            try {
                $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

                $account = $customer;
                if (!$customer->getId()) {
                    $account = $subAccount->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
                }
                if (!$account->getId()) {
                    throw new Exception('');
                }

                if ($account->getConfirmation()) {
                    $account->sendNewAccountEmail('confirmation', '', Mage::app()->getStore()->getId());
                    $this->_getSession()->addSuccess($this->__('Please, check your email for confirmation key.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('This email does not require confirmation.'));
                }
                $this->_getSession()->setUsername($email);
                $this->_redirectSuccess($this->_getUrl('*/*/index', array('_secure' => true)));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Wrong email.'));
                $this->_redirectError($this->_getUrl('*/*/*', array('email' => $email, '_secure' => true)));
            }
            return;
        }

        // output form
        $this->loadLayout();

        $this->getLayout()->getBlock('accountConfirmation')
            ->setEmail($this->getRequest()->getParam('email', $email));

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
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

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_redirect('*/*/forgotpassword');
                return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            $subAccount = $this->_getModel('cminds_multiuseraccounts/subAccount')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

            $account = $customer;
            if (!$customer->getId()) {
                // try with sub account
                $account = $subAccount->loadByEmail($email);
            }

            if ($account->getId()) {
                try {
                    $newResetPasswordLinkToken =  $this->_getHelper('customer')->generateResetPasswordLinkToken();
                    $account->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $account->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    $this->_getSession()->addError($exception->getMessage());
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }
            }
            $this->_getSession()
                ->addSuccess( $this->_getHelper('customer')
                    ->__('If there is an account associated with %s you will receive an email with a link to reset your password.',
                        $this->_getHelper('customer')->escapeHtml($email)));
            $this->_redirect('*/*/');
            return;
        } else {
            $this->_getSession()->addError($this->__('Please enter your email.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }
    }

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     */
    public function resetPasswordAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $customerId = (int) $this->getRequest()->getQuery('id');
        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);
            $this->loadLayout();
            // Pass received parameters to the reset forgotten password form
            $this->getLayout()->getBlock('resetPassword')
                ->setCustomerId($customerId)
                ->setResetPasswordLinkToken($resetPasswordLinkToken);
            $this->renderLayout();
        } catch (Exception $exception) {
            $this->_getSession()->addError( $this->_getHelper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/forgotpassword');
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $customerId
     * @param string $resetPasswordLinkToken
     * @throws Mage_Core_Exception
     */
    protected function _validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
    {
        if (!is_int($customerId)
            || !is_string($resetPasswordLinkToken)
            || empty($resetPasswordLinkToken)
            || empty($customerId)
            || $customerId < 0
        ) {
            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Invalid password reset token.'));
        }

        if (strpos($resetPasswordLinkToken, Cminds_MultiUserAccounts_Model_SubAccount::KEY_SIGN) === FALSE) {
            $account = $this->_getModel('customer/customer')->load($customerId);
        } else {
            $account = $this->_getModel('cminds_multiuseraccounts/subAccount')->load($customerId);
        }
        if (!$account || !$account->getId()) {
            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Wrong customer account specified.'));
        }

        $customerToken = $account->getRpToken();
        if (strcmp($customerToken, $resetPasswordLinkToken) != 0 || $account->isResetPasswordLinkTokenExpired()) {
            throw Mage::exception('Mage_Core', $this->_getHelper('customer')->__('Your password reset link has expired.'));
        }
    }

    /**
     * Reset forgotten password
     * Used to handle data recieved from reset forgotten password form
     */
    public function resetPasswordPostAction()
    {
        $resetPasswordLinkToken = (string) $this->getRequest()->getQuery('token');
        $customerId = (int) $this->getRequest()->getQuery('id');
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken);
        } catch (Exception $exception) {

            $this->_getSession()->addError( $exception->getMessage());
            $this->_getSession()->addError( $this->_getHelper('customer')->__('Your password reset link has expired.'));
            $this->_redirect('*/*/');
            return;
        }

        $errorMessages = array();
        if (iconv_strlen($password) <= 0) {
            array_push($errorMessages, $this->_getHelper('customer')->__('New password field cannot be empty.'));
        }
        /** @var $customer Mage_Customer_Model_Customer */
        if (strpos($resetPasswordLinkToken, Cminds_MultiUserAccounts_Model_SubAccount::KEY_SIGN) === FALSE) {
            $account = $this->_getModel('customer/customer')->load($customerId);
        } else {
            $account = $this->_getModel('cminds_multiuseraccounts/subAccount')->load($customerId);
        }

        $account->setPassword($password);
        $account->setPasswordConfirmation($passwordConfirmation);
        $account->setConfirmation($passwordConfirmation);

        $validationErrorMessages = $account->validate();
        if (is_array($validationErrorMessages)) {
            $errorMessages = array_merge($errorMessages, $validationErrorMessages);
        }

        if (!empty($errorMessages)) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            foreach ($errorMessages as $errorMessage) {
                $this->_getSession()->addError($errorMessage);
            }
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }

        try {
            // Empty current reset password token i.e. invalidate it
            $account->setRpToken(null);
            $account->setRpTokenCreatedAt(null);
            $account->setConfirmation(null);
            $account->save();
            $this->_getSession()->addSuccess( $this->_getHelper('customer')->__('Your password has been updated.'));
            $this->_redirect('*/*/login');
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot save a new password.'));
            $this->_redirect('*/*/resetpassword', array(
                'id' => $customerId,
                'token' => $resetPasswordLinkToken
            ));
            return;
        }
    }
	
    /**
     * Get Helper
     *
     * @param string $path
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($path)
    {
        return Mage::helper($path);
    }

    /**
     * Get model by path
     *
     * @param string $path
     * @param array|null $arguments
     * @return false|Mage_Core_Model_Abstract
     */
    public function _getModel($path, $arguments = array())
    {
        return Mage::getModel($path, $arguments);
    }

    /**
     * Get Url method
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function _getUrl($url, $params = array())
    {
        return Mage::getUrl($url, $params);
    }

    /**
     * Get App
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return Mage::app();
    }	
}