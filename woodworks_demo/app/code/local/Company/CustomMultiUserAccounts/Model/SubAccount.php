<?php

class Company_CustomMultiUserAccounts_Model_SubAccount extends Cminds_MultiUserAccounts_Model_SubAccount
{

    public function getPermissionLabel()
    {
        $permission = '';
        $permission = Mage::getModel('company_custommultiuseraccounts/subAccount_permission')->getOptionText($this->getPermission());
        return $permission;
    }

}
