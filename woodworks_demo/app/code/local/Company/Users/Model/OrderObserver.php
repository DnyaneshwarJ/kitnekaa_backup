<?php

class Company_Users_Model_OrderObserver extends Mage_Core_Model_Abstract
{

    public function saveCheckoutCustomDataToOrder($observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();
        //Mage::helper('users')->printPre($order->getId());die;
        $session=Mage::getSingleton('customer/session');

        if(!$session->getSubAccount())
        {
            $customer=$session->getCustomer();
            $order_by=$customer->getFirstname().' '.$customer->getLastname();
        }
        else
        {
            $customer=$session->getSubAccount();
            $order_by=$customer->getFirstname().' '.$customer->getLastname();
        }

        $company_id= $session->getCustomer()->getCompanyId();
        $company=Mage::getModel('users/company')->load($company_id);
        //$flat_order = Mage::getModel('sales/order')->load($order->getId());
        // write to the database
        $order->setCompanyId($company->getCompanyId());
        $order->setCompanyName($company->getCompanyName());
        $order->setOrderBy($order_by);
        //$flat_order->setId($order->getId())->save();

    }

    public  function salesOrderGridCollectionLoadBefore($observer)
    {
       $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->join('sales_flat_order', 'main_table.entity_id=sales_flat_order.entity_id', array('company_name'));
    }
}