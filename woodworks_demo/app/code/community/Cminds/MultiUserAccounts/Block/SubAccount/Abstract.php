<?php

class Cminds_MultiUserAccounts_Block_SubAccount_Abstract extends Mage_Core_Block_Template
{
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getInfoBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('cminds_multiuseraccounts/subAccount_widget_info')
            ->setObject($this->getFormData());

        return $nameBlock->toHtml();
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('customer/account/addsubaccountpost');
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $formData = Mage::getSingleton('customer/session')->getSubUserFormData(true);
            $data = new Varien_Object();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Get back url in account dashboard
     *
     * This method is copypasted in:
     * Mage_Wishlist_Block_Customer_Wishlist  - because of strange inheritance
     * Mage_Customer_Block_Address_Book - because of secure url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/subAccount');
    }
}
