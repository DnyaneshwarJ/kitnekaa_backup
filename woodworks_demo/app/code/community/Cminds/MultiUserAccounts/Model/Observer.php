<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Model_Observer
{
    public function checkOrderCreationAuth($observer)
    {
        $helper = Mage::helper('cminds_multiuseraccounts');

        if (!$helper->hasCreateOrderPermission()) {
            throw Mage::exception('Mage_Core', $helper->__('No Order creation permission for this account')
            );
        }else{
            $subAccount = $helper->isSubAccountMode();
            if($subAccount){
                $observer->getOrder()->setSubaccountId($subAccount->getId());
            }
        }
        return;
    }
} 