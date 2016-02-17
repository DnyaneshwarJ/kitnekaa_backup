<?php

class Kitnekaa_Core_ValidationController extends Mage_Core_Controller_Front_Action
{
    public function validateDependUniqueUpdateAction()
    {
        $model=$this->getRequest()->getPost('model');
        $field=$this->getRequest()->getPost('field');
        $value=$this->getRequest()->getPost('value');
        $depend_on=$this->getRequest()->getPost('depend_on');
        $depend_value=$this->getRequest()->getPost('depend_value');
        $old_value=$this->getRequest()->getPost('old_value');

        $result_value=Mage::getModel($model)->getCollection()
            ->addFieldToFilter($field,$value)
            ->addFieldToFilter($depend_on,$depend_value)
            ->getFirstItem();
        if(count($result_value->getData())>0 && $result_value->getData($field)!=$old_value)
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }

    public function validateDependUniqueAction()
    {
        $model=$this->getRequest()->getPost('model');
        $field=$this->getRequest()->getPost('field');
        $value=$this->getRequest()->getPost('value');
        $depend_on=$this->getRequest()->getPost('depend_on');
        $depend_value=$this->getRequest()->getPost('depend_value');
        $result_value=Mage::getModel($model)->getCollection()
            ->addFieldToFilter($field,$value)
            ->addFieldToFilter($depend_on,$depend_value)
            ->getFirstItem();
        if(count($result_value->getData())>0)
        {
            echo 0;
        }
        else
        {
            echo 1;
        }

        exit;
    }

    public function validateUniqueEmailAction()
    {
        $value=$this->getRequest()->getPost('value');
        $result_value=Mage::getModel("customer/customer")->getCollection()
            ->addFieldToFilter("email",$value)
            ->getFirstItem();

        $sub_account_exist = Mage::getModel('cminds_multiuseraccounts/subAccount')->getCollection()
            ->addFieldToFilter('email', $value)
            ->getFirstItem()->getData();

        if(count($result_value->getData())>0 || count($sub_account_exist)>0)
        {
            echo 0;
        }
        else
        {
            echo  1;
        }

        exit;
    }

    public function validateUniqueCompanyAction()
    {
        $value=$this->getRequest()->getPost('value');
        $old_value=$this->getRequest()->getPost('old_value');
        if($value==Mage::helper('users')->getBuyerTypeLabel(0)) {
          echo 1;
        }
        else
        {
            $company = Mage::getModel('users/company')->getCollection()
                ->addFieldToFilter('company_name',$value)
                ->getFirstItem();
            $company_count = count($company->getData());
            if ($company_count > 0 && strtolower($old_value)!=strtolower($value)) {
                echo 0;
            } else {
                echo 1;
            }
        }
        exit;
    }
}