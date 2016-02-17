<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Helper_Customer_Data extends Mage_Customer_Helper_Data
{

    public function getEmailConfirmationUrl($email = null)
    {
//        // if SubAccount
//        $subAccount = Mage::getModel('cminds_multiuseraccounts/subAccount')
//            ->setWebsiteId(Mage::app()->getWebsite()->getId())
//            ->loadByEmail($email);
//        if ($subAccount->getId()) {
//            return Mage::helper('cminds_multiuseraccounts')->getEmailConfirmationUrl($email);
//        }
        return parent::getEmailConfirmationUrl($email);
    }
}