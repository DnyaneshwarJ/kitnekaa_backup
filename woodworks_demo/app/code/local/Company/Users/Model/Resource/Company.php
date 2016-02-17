<?php
class Company_Users_Model_Resource_Company extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        // parent::_construct();
        $this->_init('users/company','company_id');
    }
}