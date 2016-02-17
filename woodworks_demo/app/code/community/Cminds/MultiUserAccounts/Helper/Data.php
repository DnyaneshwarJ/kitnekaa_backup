<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Helper_Data extends Mage_Customer_Helper_Data
{

    const ENABLED_KEY = 'subAccount/general/enable';

    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function isEnabled()
    {
        $cmindsCore = Mage::getModel("cminds/core");

        if($cmindsCore) {
            $cmindsCore->validateModule('Cminds_Marketplace');
        } else {
            throw new Mage_Exception('Cminds Core Module is disabled or removed');
        }

        $storeId = $this->getStoreId() ? $this->getStoreId() : null;
        return (bool)Mage::getStoreConfig(self::ENABLED_KEY, $storeId);
    }
//
//    public function getEmailConfirmationUrl($email = null)
//    {
//        return $this->_getUrl('customer/account/subAccountConfirmation', array('email' => $email));
//    }

    public function hasWritePermission()
    {
        if ($subAccount = $this->isSubAccountMode()) {
            return $subAccount->hasWritePermission();
        }
        return true;
    }

    public function hasCreateOrderPermission()
    {
        if ($subAccount = $this->isSubAccountMode()) {
            return $subAccount->hasCreateOrderPermission();
        }
        return true;
    }

    public function canViewAllOrders()
    {
        if ($subAccount = $this->isSubAccountMode()) {
            return $subAccount->canViewAllOrders();
        }
        return true;
    }

    public function isSubAccountMode()
    {
        $subAccount = Mage::getSingleton('customer/session')->getSubAccount();
        if ($subAccount && $subAccount->getId()) {
            return $subAccount;
        }
        return false;
    }
}