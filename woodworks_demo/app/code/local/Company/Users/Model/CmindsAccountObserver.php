<?php

class Company_Users_Model_CmindsAccountObserver extends Mage_Core_Model_Abstract
{

    public function check_permission_redirect($observer)
    {
       // Mage::helper('users')->printPre($observer->getLayout());

        //exit;
        //if(Mage::helper('cminds_multiuseraccounts')->isSubAccountAdmin())
        //{
         //   Mage::helper('users')->printPre(Mage::getSingleton('customer/session')->getSubAccount());die;
       // }
    }
}