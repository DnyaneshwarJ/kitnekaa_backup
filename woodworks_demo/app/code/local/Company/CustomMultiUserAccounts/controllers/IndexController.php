<?php

class Company_CustomMultiUserAccounts_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        $action   = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, TRUE);
        }
    }

    public function indexAction()
    {
        if(Mage::getSingleton('customer/session')->getSubAccount())
        {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');
            $this->renderLayout();
        }
        else
        {
            $this->_forward('noRoute');
        }
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
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

    /** subaccount edit action */
    public function editSubAccountPostAction()
    {
        $subAccount = $this->_getSession()->getSubAccount();

        //$subAccountId = $this->getRequest()->getParam('id');
        //$subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccount->getId());

        $data = $this->getRequest()->getPost();

       // Mage::helper('users')->printPre($this->_getSession());die;
        if ($data) {
            $errors = array();

            // If password change was requested then add it to common validation scheme
            if ($this->getRequest()->getPost('change_password')) {
                $currPass = $this->getRequest()->getPost('current_password');
                $newPass  = $this->getRequest()->getPost('password');
                $confPass = $this->getRequest()->getPost('password_confirmation');

                $oldPass = $subAccount->getPasswordHash();

                if ($this->_getHelper('core/string')->strpos($oldPass, ':')) {
                    list($_salt, $salt) = explode(':', $oldPass);
                }
                else {
                    $salt = FALSE;
                }

                if ($subAccount->hashPassword($currPass, $salt) == $oldPass) {
                    if (strlen($newPass)) {
                        /**
                         * Set entered password and its confirmation - they
                         * will be validated later to match each other and be of right length
                         */
                        $subAccount->setPassword($newPass);
                        $subAccount->setPasswordConfirmation($confPass);
                    }
                    else {
                        $errors[] = $this->__('New password field cannot be empty.');
                    }
                }
                else {
                    $errors[] = $this->__('Invalid current password');
                }
            }
            else { // no change password
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
                //var_dump($errors);die;
                $this->_redirect('*/*/index');
                //return $this;
            }

        }
        else {
            $this->_getSession()->setSubUserFormData($this->getRequest()->getPost());
            $this->_getSession()->addError('Missing Data');
            $this->_redirect('*/*/index');
            //return $this;
        }

        try {
            //                $subAccount->setConfirmation(null);
            $subAccount->save();
            $this->_getSession()->addSuccess($this->__('User information has been saved.'));

            $this->_redirect('*/*/index');
            //return;
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->setSubUserFormData($this->getRequest()->getPost())
                 ->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->setSubUserFormData($this->getRequest()->getPost())
                 ->addException($e, $this->__('Cannot save the user.'));
        }

        $this->_redirect('*/*/index');

    }

    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     */
    protected function _forward($action, $controller = null, $module = null, array $params = null)
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
                ->setDispatched(false);
    }
}
