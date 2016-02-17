<?php
/**
 * @author CreativeMindsSolutions
 */
  
class Cminds_MultiUserAccounts_Block_Customer_Account_Dashboard_Hello extends Mage_Customer_Block_Account_Dashboard_Hello {

    public function getCustomerName()
    {
        $name = Mage::getSingleton('customer/session')->getCustomer()->getName();
        $helper = Mage::helper('cminds_multiuseraccounts');

        if ($subAccount = $helper->isSubAccountMode()) {
            $name = $subAccount->getName();
        }

        return $name;
    }

}