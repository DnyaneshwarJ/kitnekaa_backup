<?php

class Cminds_MultiUserAccounts_Model_Resource_SubAccount_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('cminds_multiuseraccounts/subAccount');
    }
}
