<?php

class UnirgyCustom_DropshipQuote2sale_Model_OrderObserver
{
    public function set_quote_to_order($observer)
    {
        $order= $observer->getEvent()->getOrder();
        $quote= $observer->getEvent()->getQuote();
        $order->setVendorId($quote->getVendorId());
        return $order;
    }

    public function set_quote_item_to_order_item($observer)
    {
        $order_item= $observer->getEvent()->getOrderItem();
        $quote_item= $observer->getEvent()->getQuoteItem();
        $order_item->setUdropshipVendor($quote_item->getUdropshipVendor());
        return $order_item;
    }
}