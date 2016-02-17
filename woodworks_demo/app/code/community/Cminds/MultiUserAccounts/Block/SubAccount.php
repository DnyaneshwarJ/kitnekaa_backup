<?php

class Cminds_MultiUserAccounts_Block_SubAccount extends Mage_Core_Block_Template
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $items = Mage::getModel('cminds_multiuseraccounts/subAccount')->getSubAccounts($this->getCustomer());
        $items->setOrder('email', 'asc');

        $this->setItems($items);
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getSubAccounts()
    {
        return Mage::getModel('cminds_multiuseraccounts/subAccount')->getSubAccounts($this->getCustomer());
    }

    public function getAddSubAccountUrl()
    {
        return $this->getUrl('customer/account/addSubAccount');
    }

    public function getDeleteSubAccountUrl($id)
    {
        return $this->getUrl('customer/account/deleteSubAccount', array('id' => $id));
    }

    public function getEditSubAccountUrl($id)
    {
        return $this->getUrl('customer/account/editSubAccount/id', array('id' => $id));
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

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'multiuseraccounts.customer.subaccount.pager')
            ->setCollection($this->getItems());
        $this->setChild('pager', $pager);
        $this->getItems()->load();
        return $this;
    }
}
