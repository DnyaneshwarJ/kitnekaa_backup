<?php

class UnirgyCustom_DropshipQuote2sale_Model_ExtendedObserver extends Kitnekaa_Quote2SalesCustom_Model_Observer
{

    function setCustomDataOnQuoteSave($observer)
    {
        //echo "sad1";die;
        parent::setCustomDataOnQuoteSave($observer);
        if(Mage::helper('udquote2sale')->getVendorId())
        {
            $quote = $observer->getEvent()->getQuote();
            $quote->setVendorId(Mage::helper('udquote2sale')->getVendorId());
        }
        return $observer;
    }


}