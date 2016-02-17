<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Model_Customer_Customer extends Mage_Customer_Model_Customer
{
    public function authenticate($login, $password)
    {
        // We need to websiteId value before load By Email
        $subAccountMode = false;
        $websiteId = $this->getWebsiteId();
        $useSubAccount = Mage::helper('cminds_multiuseraccounts')->isEnabled();

        $this->loadByEmail($login);
        $account = $this;

        if (!$account->getId() && $useSubAccount){
            // No Main Account found try with SubAccount
            $account = Mage::getModel('cminds_multiuseraccounts/subAccount')->setWebsiteId($websiteId);

            $account->loadByEmail($login);
            $subAccountMode = true;
        }

        if ($account->getConfirmation() && $account->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$account->validatePassword($password)) {

            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }

        if ($subAccountMode){
            $this->load($account->getParentCustomerId());
            $customerSession = Mage::getSingleton('customer/session');
            $customerSession->setSubAccount($account);
            $beforeUrl = $customerSession->getBeforeAuthUrl();

            if(strpos($beforeUrl,'customer/account/subAccount') !== false){
                $customerSession->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            }
        }

        Mage::dispatchEvent('customer_customer_authenticated', array(
            'model' => $this,
            'password' => $password,
        ));

        return true;
    }
}