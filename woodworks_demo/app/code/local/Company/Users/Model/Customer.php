<?php

class Company_Users_Model_Customer extends Cminds_MultiUserAccounts_Model_Customer_Customer
{
    public function  delete()
    {
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_read');

        $conn->delete(
            $coreResource->getTableName('users/company'),
            array('customer_id= ? '=>$this->getId())
        );
        //Mage::getModel('users/company')
        parent::delete();
    }
}
