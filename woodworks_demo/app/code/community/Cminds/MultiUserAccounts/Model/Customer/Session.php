<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Model_Customer_Session extends Mage_Customer_Model_Session
{
    /**
     * Customer object
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_subAccount;

    public function setSubAccount(Cminds_MultiUserAccounts_Model_SubAccount $subAccount)
    {
        // check if customer is not confirmed
        if ($subAccount->isConfirmationRequired()) {
            if ($subAccount->getConfirmation()) {
                return $this->_logout();
            }
        }
        $this->_subAccount = $subAccount;
        $this->setSubAccountId($subAccount->getId());
        // save customer as confirmed, if it is not ( remove confirmation key )
        if ((!$subAccount->isConfirmationRequired()) && $subAccount->getConfirmation()) {
            $subAccount->setConfirmation(null)->save();
            $subAccount->setIsJustConfirmed(true);
        }
        return $this;
    }

    public function getSubAccount()
    {
        if ($this->_subAccount instanceof Cminds_MultiUserAccounts_Model_SubAccount) {
            return $this->_subAccount;
        }

        $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount');
        if ($this->getSubAccountId()) {
            $subAccount->load($this->getSubAccountId());
            $this->setSubAccount($subAccount);
        }

        return $this->_subAccount;
    }

    public function removeSubAccount()
    {
        $this->_subAccount = null;
        $this->setSubAccountId(null);
        return $this;
    }

    protected function _logout()
    {
        $this->removeSubAccount();
        return parent::_logout();
    }
}