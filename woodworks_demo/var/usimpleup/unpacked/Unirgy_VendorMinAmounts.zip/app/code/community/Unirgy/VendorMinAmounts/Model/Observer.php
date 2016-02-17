<?php

class Unirgy_VendorMinAmounts_Model_Observer
{
    protected $_cartUpdateActionFlag=false;
    public function setIsCartUpdateActionFlag($flag)
    {
        $this->_cartUpdateActionFlag=(bool)$flag;
        return $this;
    }
    public function controller_action_predispatch_checkout_cart_add($observer)
    {
        $this->setIsCartUpdateActionFlag(true);
    }
    public function controller_action_predispatch_checkout_cart_updatePost($observer)
    {
        $this->setIsCartUpdateActionFlag(true);
    }
    public function sales_quote_load_after($observer)
    {
        $hl = Mage::helper('udropship');
        $quote = $observer->getQuote();
        $qId = $quote->getId();
        if ($hl->isSkipQuoteLoadAfterEvent($qId)
            || $this->_cartUpdateActionFlag
        ) {
            return;
        }

        $hlp = Mage::helper('udropship/protected');
        $items = $observer->getQuote()->getAllItems();
        $subtotalByVendor = array();
        foreach ($items as $item) {
            if (empty($subtotalByVendor[$item->getUdropshipVendor()])) {
                $subtotalByVendor[$item->getUdropshipVendor()] = 0;
            }
            if (Mage::helper('tax')->priceIncludesTax()) {
                $subtotalByVendor[$item->getUdropshipVendor()] += $item->getBaseRowTotalInclTax();
            } else {
                $subtotalByVendor[$item->getUdropshipVendor()] += $item->getBaseRowTotal();
            }
            #$subtotalByVendor[$item->getUdropshipVendor()] -= $item->getBaseDiscountAmount();
        }
        foreach ($subtotalByVendor as $vId=>$subtotal) {
            $vendor = Mage::helper('udropship')->getVendor($vId);
            $minOrderAmount = null;
            if (!$vendor->getId()) continue;
            $minOrderAmount = $this->getVendorMinOrderAmount($observer->getQuote(), $vendor, $subtotal);
            if ($minOrderAmount !== false && $subtotal < $minOrderAmount) {
                $this->addVendorMinOrderAmountError($observer->getQuote(), $vendor, $minOrderAmount, $subtotal);
            }
        }
    }

    public function getVendorMinOrderAmount($quote, $vendor, $subtotal)
    {
        return Mage::helper('udminamount')->getVendorMinOrderAmount($quote, $vendor, $subtotal);
    }

    public function addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal)
    {
        return Mage::helper('udminamount')->addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal);
    }

    public function udropship_process_vendor_carrier_single_rate_result($observer)
    {
        $request  = $observer->getRequest();
        $rate     = $observer->getRate();
        $udMethod = $observer->getUdmethod();
        $_udMethod = $udMethod instanceof Varien_Object ? $udMethod->getShippingCode() : $udMethod;

        $vendorSubtotal = 0;
        foreach ($request->getAllItems() as $item) {
            if (Mage::helper('tax')->priceIncludesTax()) {
                $vendorSubtotal += $item->getBaseRowTotalInclTax()-$item->getBaseDiscountAmount();
            } else {
                $vendorSubtotal += $item->getBaseRowTotal()-$item->getBaseDiscountAmount();
            }
        }

        $freeMethods = explode(',', Mage::getStoreConfig('carriers/udropship/free_method', $request->getStoreId()));
        $vendor = Mage::helper('udropship')->getVendor($request->getVendorId());
        $freeShippingSubtotal = null;
        if ($vendor->getId()) {
            $freeShippingSubtotal = $vendor->getFreeShippingSubtotal();
        }
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = Mage::getStoreConfig('carriers/udropship/vendor_free_shipping_subtotal', $request->getStoreId());
        }
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = false;
        }
        if (in_array($_udMethod, $freeMethods)
            && Mage::getStoreConfigFlag('carriers/udropship/free_shipping_allowed', $request->getStoreId())
            && Mage::getStoreConfigFlag('carriers/udropship/free_shipping_enable', $request->getStoreId())
            && $freeShippingSubtotal!==false
            && $freeShippingSubtotal <= $vendorSubtotal
        ) {
            $rate->setPrice('0.00');
        }
        return $this;
    }

}
