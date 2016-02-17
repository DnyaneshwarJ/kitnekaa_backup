<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Adminhtml_SubAccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize customer by ID specified in request
     *
     */
    protected function _initCustomer($key)
    {
        $customerId = (int)$this->getRequest()->getParam($key);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Customer Sub Account ajax action
     *
     */
    public function subAccountGridAction()
    {
        $this->_initCustomer('id');
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Customer Sub Account ajax action
     * Not used right now:
     * it will be useful if we want a grid with all the sub account for all customer
     */
    public function gridAction()
    {
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Sub Account details
     *
     */
    public function editAction()
    {
        $this->_title($this->__('Sub Account'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('cminds_multiuseraccounts/subAccount');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('cminds_multiuseraccounts')->__('This sub account no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getEmail());

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('sub_account', $model);

        // 5. Build edit form
        $this->loadLayout()
            ->_addBreadcrumb(
                $id ? Mage::helper('cminds_multiuseraccounts')->__('Edit Sub Account') : Mage::helper('cminds_multiuseraccounts')->__('New Sub Account'),
                $id ? Mage::helper('cminds_multiuseraccounts')->__('Edit Sub Account') : Mage::helper('cminds_multiuseraccounts')->__('New Sub Account'));
        $this->renderLayout();
    }

    /**
     * Sub Account details
     *
     */
    public function newAction()
    {
        $this->_title($this->__('Sub Account'));

        $id = null;
        $model = Mage::getModel('cminds_multiuseraccounts/subAccount');

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('sub_account', $model);

        $this->loadLayout()
            ->_addBreadcrumb(
                Mage::helper('cminds_multiuseraccounts')->__('New Sub Account'),
                Mage::helper('cminds_multiuseraccounts')->__('New Sub Account'));
        $this->renderLayout();
    }

    /**
     * Sub Account save
     *
     */
    public function editPostAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            // this is not sent by Post but in the url
            $subAccountId = $this->getRequest()->getParam('id');
            $subAccountData = $data['subaccount'];
            $parentCustomerId = $subAccountData['parent_customer_id'];

            try {
                $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount');

                if ($subAccountId) {
                    $subAccount->load($subAccountId);
                }

                if (!empty($subAccountData['new_password'])){
                    $newPassword = $subAccountData['new_password'];
                    $subAccount->setPassword($newPassword);
                    $subAccount->sendPasswordReminderEmail();
                }
                unset($subAccountData['new_password']);

                $subAccount->addData($subAccountData);
                $subAccount->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('The Sub Account has been saved.')
                );

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setSubAccountData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/subAccount/edit', array('id' => $subAccountId)));
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('adminhtml')->__('An error occurred while saving the Sub Account.'));
                $this->_getSession()->setSubAccountData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/subAccount/edit', array('id' => $subAccountId)));
                return;
            }
        }

        $this->getResponse()->setRedirect($this->getUrl('*/customer/edit/tab/customer_info_tabs_customer_edit_tab_subaccount', array('id' => $parentCustomerId)));
    }

    /**
     * add sub account
     */
    public function newPostAction()
    {
        $this->_initCustomer('parent_customer_id');
        $parentCustomer = Mage::registry('current_customer');
        $parentCustomerId = $parentCustomer->getId();

        $data = $this->getRequest()->getPost();
        if ($data) {
            $subAccountData = $data['subaccount'];

            try {
                $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount');

                $subAccountData['store_id'] = $parentCustomer->getStoreId();
                $subAccountData['website_id'] = $parentCustomer->getWebsiteId();

                $subAccount->setPassword($subAccountData['new_password']);
                $subAccount->sendPasswordReminderEmail();

                unset($subAccountData['new_password']);
                unset($subAccountData['password_confirmation']);

                $subAccount->addData($subAccountData);
                $subAccount->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('The Sub Account has been saved.')
                );

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setSubAccountData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/subAccount/edit', array('parent_customer_id' => $parentCustomerId)));
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('adminhtml')->__('An error occurred while saving the Sub Account.'));
                $this->_getSession()->setSubAccountData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/subAccount/edit', array('parent_customer_id' => $parentCustomerId)));
                return;
            }
        }

        $this->getResponse()->setRedirect($this->getUrl('*/customer/edit/tab/customer_info_tabs_customer_edit_tab_subaccount', array('id' => $parentCustomerId)));
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $data = $this->getRequest()->getParam('subaccount');

        $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount');
        if ($id = $this->getRequest()->getParam('id')) {
            $subAccount->load($id);
            $websiteId = $subAccount->getWebsiteId();
        }else{
            if(isset($data['new_password'])){
                $data['password'] = $data['new_password'];
            }

            $this->_initCustomer('parent_customer_id');
            $websiteId = Mage::registry('current_customer')->getWebsiteId();
        }

        $subAccount->addData($data);
        $errors = $subAccount->validate();

        if ($errors !== true) {
            foreach ($errors as $error) {
                $this->_getSession()->addError($error);
            }
            $response->setError(true);
        }

        # additional validate email
        if (!$response->getError()) {
            # Trying to load customer with the same email and return error message
            # if customer with the same email address exisits

            $checkCustomer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
            $checkCustomer->loadByEmail($subAccount->getEmail());
            $subAccountcheck = Mage::getModel('cminds_multiuseraccounts/subAccount')->setWebsiteId($websiteId);
            $subAccountcheck->loadByEmail($subAccount->getEmail());

            if ($checkCustomer->getId()
                || ($subAccountcheck->getId() &&
                    ($subAccountcheck->getId() != $subAccount->getId()))
            ) {
                $response->setError(1);
                $this->_getSession()->addError(
                    Mage::helper('adminhtml')->__('Customer with the same email already exists.')
                );
            }
        }

        if ($response->getError()) {
            $this->_initLayoutMessages('adminhtml/session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction()
    {
        $parentCustomerId = null;
        $subAccountId = $this->getRequest()->getParam('id');
        $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')->load($subAccountId);

        try {
            if ($subAccount->getId()) {
                $email = $subAccount->getEmail();
                $parentCustomerId = $subAccount->getParentCustomerId();
                $subAccount->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('User %s has been deleted.', $email));

            } else {
                Mage::getSingleton('adminhtml/session')->addError('Invalid User');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('An Error occurred');
        }

        if($parentCustomerId){
            $this->getResponse()->setRedirect($this->getUrl('*/customer/edit/tab/customer_info_tabs_customer_edit_tab_subaccount', array('id' => $parentCustomerId)));
        }else{
            $this->getResponse()->setRedirect($this->getUrl('*/customer/index'));
        }
    }
}