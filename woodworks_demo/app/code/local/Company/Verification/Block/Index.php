<?php

class Company_Verification_Block_Index extends Mage_Core_Block_Template
{

    protected $customer;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $customer_id = Mage::getSingleton('core/session')->getKitCustomerId();
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getStore());
        $this->customer = $customer->load($customer_id);
    }

    public function isMobileNumberVerified()
    {
        return $this->customer->getMobNoVerification();
    }
}