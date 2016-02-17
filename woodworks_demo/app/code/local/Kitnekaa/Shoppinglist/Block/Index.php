<?php

class Kitnekaa_Shoppinglist_Block_Index extends Mage_Core_Block_Template
{

    public function getShoppinglistModel()
    {
        return Mage::getModel('shoppinglist/shoppinglist');
    }

    public function getShoppinglistItemsModel()
    {
        return Mage::getModel('shoppinglist/shoppinglistitems');
    }

    public function getShoppinglistAttachmentsModel()
    {
        return Mage::getModel('shoppinglist/shoppinglistfiles');
    }

    public function getCurrentCompanyId()
    {
        $customer = $this->getCustomerData();
       /* if (!is_null($customer->getCompanyId())) {
            $_company_id = $customer->getCompanyId();
        } else {
            $_company_id = $customer->getId();
        }*/
        $_company_id = $customer->getCompanyId();
        return $_company_id;
    }

    public function getCustomerData()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        return $customer;
    }

    public function getShoppingLists()
    {
        $shopp_lists = $this->getShoppinglistModel()
            ->getCollection()
            ->addFieldToFilter('company_id', $this->getCurrentCompanyId());

        return $shopp_lists;
    }

    public function getCustomerById($id)
    {
        if (is_null($id)) {
            $id = 0;
        }
        $customerData = Mage::getModel('customer/customer')->load($id);

        return $customerData;
    }

    public function getSelectedList()
    {
        $shopp_list_id = $this->getShoppinglistModel()
            ->getCollection()
            ->addFieldToFilter('company_id', $this->getCurrentCompanyId())
            ->getFirstItem();;

        return $shopp_list_id;
    }


    public function getShoppingListItems($list_id)
    {
        $shopp_list_items = $this->getShoppinglistItemsModel()
            ->getCollection()
            ->addFieldToFilter('list_id', $list_id);

        return $shopp_list_items;
    }

    public function getShoppingAttachments($list_item_id)
    {
        $shopp_list_attachments = $this->getShoppinglistAttachmentsModel()
            ->getCollection()
            ->addFieldToFilter('list_item_id', $list_item_id);

        return $shopp_list_attachments;
    }

    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}