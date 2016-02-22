<?php
class Company_Users_Model_Company extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('users/company');
    }

    public function getStates(){
    	$statesArray = array();
        $statesArray[0] = 'Not Verified';
        $statesArray[1] = 'Verified';
		return $statesArray;
    }

    public function  loadByCustomerId($_customer_id)
    {
        $collection=$this->getCollection()
            ->addFieldToFilter('customer_id',$_customer_id)
            ->getFirstItem();
        return $collection;
    }
}