<?php
class Company_Users_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getCustomerAttributeValue($customer,$attribute_code)
    {
        return $customer->getResource()->getAttribute($attribute_code)->getFrontend()->getValue($customer);
    }

    public  function  getParentCustomerData($customer)
    {
        if(is_null($customer->getParentCustomerId()))
        {
            return $customer;
        }
        else
        {
            return Mage::getModel('customer/customer')->load($customer->getParentCustomerId());
        }

    }

    public function isCompanyExist($customer,$company_name,$company_type)
    {
        if($company_type==1) {
            $company = Mage::getModel('users/company')->getCollection()
                ->addFieldToFilter('company_name', $company_name)
                ->getFirstItem();
            $company_id = $this->getCustomerAttributeValue($customer, 'company_id');
            $company_count = count($company->getData());

            if ($company_count > 0 && $company_id != $company->getCompanyId()) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }

    }

    public function printPre($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    public function getCompany($customer_id)
    {
        $company=Mage::getModel('users/company')->getCollection()
            ->addFieldToFilter('company_id',$customer_id)
            ->getFirstItem();

        return  $company;

    }

    public function getCurrentCompanyUser()
    {
        $session=Mage::getSingleton('customer/session');

        if(!$session->getSubAccount())
        {
            $user=$session->getCustomer();
        }
        else
        {
            $user=$session->getSubAccount();
        }

        return $user;
    }

    public function getUserFullName($user)
    {
        return $user->getFirstname().' '.$user->getLastname();
    }

    public function getBuyerTypeLabel($id)
    {
        $buyer=array(0=>$this->__('Proprietor'),1=>$this->__('Company'));
        return $buyer[$id];
    }

    public function isEmailExistInSubaccount($customer)
    {
            $sub_account_exist = Mage::getModel('cminds_multiuseraccounts/subAccount')->getCollection()
                ->addFieldToFilter('email', $customer->getEmail())
                ->getFirstItem()->getData();
                //var_dump($sub_account_exist);die;
            if (count($sub_account_exist))
            {
                return TRUE;
            } else {
                return FALSE;
            }


    }
}
	 