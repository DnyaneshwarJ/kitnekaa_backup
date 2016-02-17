<?php

class Cminds_MultiUserAccounts_Model_SubAccount extends Mage_Customer_Model_Customer
{
    const XML_PATH_IS_CONFIRM = 'subAccount/create_subAccount/confirm';
    const KEY_SIGN = '-SUB';

    private static $_isConfirmationRequired;

    function _construct()
    {
       // echo "dsad";die;
        $this->_init('cminds_multiuseraccounts/subAccount');
    }

    // We need $login for method signature compatibility
    public function authenticate($login, $password)
    {

        if (!$this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePassword($password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }

        return true;
    }

    public function getSubAccounts(Mage_Customer_Model_Customer $customer)
    {
        /** @var  $collection Cminds_MultiUserAccounts_Model_Resource_SubAccount_Collection */
        $collection = $this->getCollection();
        $collection->addFieldToFilter('parent_customer_id', $customer->getId());

        return $collection;
    }

    public function getName()
    {
        $name = '';

        $name .= $this->getFirstname();
        $name .= ' ' . $this->getLastname();

        return $name;
    }

    public function getPermissionLabel()
    {
        $permission = '';
        $permission = Mage::getModel('cminds_multiuseraccounts/subAccount_permission')->getOptionText($this->getPermission());

        return $permission;
    }

    public function validate()
    {
        $errors = array();
        if (!Zend_Validate::is(trim($this->getFirstname()), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
        }

        if (!Zend_Validate::is(trim($this->getLastname()), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $this->getEmail());
        }

        if (!Zend_Validate::is($this->getPermission(), 'Int')) {
            $errors[] = Mage::helper('customer')->__('Invalid permissions "%s".', $this->getPermission());
        }
        if (!Zend_Validate::is($this->getParentCustomerId(), 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('Invalid main account "%s".', $this->getParentCustomerId());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password, 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = Mage::helper('customer')->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getPasswordConfirmation();
        if ($password != $confirmation) {
            $errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    // Required to use the SubAccount XML_PATH_IS_CONFIRM KEY
    public function isConfirmationRequired()
    {
        if (self::$_isConfirmationRequired === null) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : null;
            self::$_isConfirmationRequired = (bool)Mage::getStoreConfig(self::XML_PATH_IS_CONFIRM, $storeId);
        }

        return self::$_isConfirmationRequired;
    }

    // Remove frontend check for admin area
    protected function _beforeDelete()
    {
        Mage::dispatchEvent('model_delete_before', array('object' => $this));
        Mage::dispatchEvent($this->_eventPrefix . '_delete_before', $this->_getEventData());
        $this->cleanModelCache();
        return $this;
    }

    public function hasWritePermission()
    {
        $permissions = Mage::getModel('cminds_multiuseraccounts/subAccount_permission')->getWritePermission();
        return (in_array($this->getPermission(), $permissions));
    }

    public function hasCreateOrderPermission()
    {
        $permissions = Mage::getModel('cminds_multiuseraccounts/subAccount_permission')->getOrderCreationPermission();
        return (in_array($this->getPermission(), $permissions));
    }

    public function canViewAllOrders()
    {
        return (1 == $this->getViewAllOrders()) ? true : false;
    }

    /**
     * Generate random confirmation key
     *
     * @return string
     */
    public function getRandomConfirmationKey()
    {
        return parent::getRandomConfirmationKey() . self::KEY_SIGN;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param Mage_Customer_Model_Customer $newResetPasswordLinkToken
     * @param string $newResetPasswordLinkToken
     * @return Mage_Customer_Model_Resource_Customer
     */
    public function changeResetPasswordLinkToken($newResetPasswordLinkToken) {
        if (is_string($newResetPasswordLinkToken) && !empty($newResetPasswordLinkToken)) {
            $newResetPasswordLinkToken = $newResetPasswordLinkToken . self::KEY_SIGN;
            $this->setRpToken($newResetPasswordLinkToken);
            $currentDate = Varien_Date::now();
            $this->setRpTokenCreatedAt($currentDate);
            $this->save();
        }
        return $this;
    }

    public function sendPasswordReminderEmail()
    {
        $this->setName($this->getFirstName() . ' '. $this->getLastName());

        parent::sendPasswordReminderEmail();

        return $this;
    }
}
