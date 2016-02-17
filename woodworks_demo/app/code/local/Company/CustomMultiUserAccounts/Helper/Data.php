<?php
class Company_CustomMultiUserAccounts_Helper_Data extends Cminds_MultiUserAccounts_Helper_Data
{
    public  function isSubAccountAdmin()
    {
        $admin=Mage::getModel('company_custommultiuseraccounts/subAccount_permission')->getAdminPermission();
        if ($subAccount = $this->isSubAccountMode())
        {
            if($subAccount->getPermission()==$admin){return TRUE;}
        }
        return FALSE;
    }


    public function hasCreateOrderPermission()
    {
        if ($subAccount = $this->isSubAccountMode()) {
            if($this->isSubAccountAdmin())
            {
                return true;
            }
            else
            {
                return $subAccount->hasCreateOrderPermission();
            }
        }
        return true;
    }
}
	 