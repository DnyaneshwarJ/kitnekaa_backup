<?php

class Unirgy_VendorMinAmounts_Helper_Data
{
    public function getVendorMinOrderAmount($quote, $vendor, $subtotal)
    {
        $minOrderAmount = $vendor->getMinimumOrderAmount();
        if ($minOrderAmount === null || $minOrderAmount === '') {
            $minOrderAmount = Mage::getStoreConfig('carriers/udsplit/minimum_vendor_order_amount', $quote->getStoreId());
        }
        if ($minOrderAmount === null || $minOrderAmount === '') {
            $minOrderAmount = Mage::getStoreConfig('carriers/udropship/minimum_vendor_order_amount', $quote->getStoreId());
        }
        if ($minOrderAmount === null || $minOrderAmount === '') {
            $minOrderAmount = false;
        }
        return $minOrderAmount;
    }

    public function addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal)
    {
        $minOrderAmountFormatted = $quote->getStore()->convertPrice($minOrderAmount, true, false);
        $quoteErr = Mage::getStoreConfig('carriers/udropship/minimum_vendor_order_amount_quote_message', $quote->getStoreId());
        $vendorErr = Mage::getStoreConfig('carriers/udropship/minimum_vendor_order_amount_message', $quote->getStoreId());
        $quote->setHasError(true)->addMessage(
            @sprintf($quoteErr, $vendor->getVendorName(), $minOrderAmountFormatted),
            'udminamountfee'.$vendor->getId()
        );
        $vendorErrors = $quote->getMinVendorOrderAmountErrors();
        if (empty($vendorErrors)) {
            $vendorErrors = array();
        }
        $vendorErrors[$vendor->getId()] = @sprintf($vendorErr, $minOrderAmountFormatted);
        $quote->setMinVendorOrderAmountErrors($vendorErrors);
        return $this;
    }
}