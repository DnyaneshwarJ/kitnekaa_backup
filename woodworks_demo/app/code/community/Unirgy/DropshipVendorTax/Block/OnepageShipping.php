<?php

class Unirgy_DropshipVendorTax_Block_OnepageShipping extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingPrice($price, $flag)
    {
        $udsDetails = $this->getAddress()->getUdropshipShippingDetails();
        if (!is_array($udsDetails)) {
            $udsDetails = Zend_Json::decode($udsDetails);
        }
        if (Mage::helper('udropship')->isUdsplitActive()) {
            $_udsDetails = @$udsDetails['methods'];
        } else {
            $_udsDetails = false;
            $uMethod = explode('_', $this->getAddress()->getUdtempRateCode(), 2);
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
            if (is_array($udsDetails) && !empty($udsDetails['methods'][$uMethodCode]['vendors'])
                && is_array($udsDetails['methods'][$uMethodCode]['vendors'])
            ) {
                $_udsDetails = $udsDetails['methods'][$uMethodCode]['vendors'];
            }
        }
        if (!is_array($_udsDetails)) {
            $_udsDetails = array();
        }
        if (!empty($_udsDetails)) {
            $shipPrice = 0;
            foreach ($_udsDetails as $vId => $_udsDetail) {
                $this->getAddress()->setUdropshipVendor($vId);
                $vShipPriceIncl = $this->helper('tax')->getShippingPrice(
                    $_udsDetail['price'],
                    $flag,
                    $this->getAddress()
                );
                $shipPrice += $vShipPriceIncl;
            }
            $shipPrice = $shipPrice;
        } else {
            $shipPrice = $this->helper('tax')->getShippingPrice(
                $price,
                $flag,
                $this->getAddress()
            );
        }
        return $this->getQuote()->getStore()->convertPrice($shipPrice, true);
    }
}