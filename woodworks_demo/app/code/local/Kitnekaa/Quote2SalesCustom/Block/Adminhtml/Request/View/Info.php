<?php
class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Request_View_Info extends Bobcares_Quote2Sales_Block_Adminhtml_Request_View_Info
{
	 public function getCompany()
    {
    	$customer = $this->getCustomer();
        $company=Mage::getModel('users/company')->load($customer->getCompanyId());
        return  $company;
    }

    public function getOnelineAddress($id)
    {
        $address=Mage::getModel('customer/address')->load($id);
        return $address->format('oneline');
    }
}
