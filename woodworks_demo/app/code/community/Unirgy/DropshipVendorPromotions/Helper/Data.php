<?php

class Unirgy_DropshipVendorPromotions_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getQuoteAddrTotal($address, $totalKey, $vId)
    {
        if ($totalKey == 'base_subtotal') {
            $qiKey = 'base_row_total';
        } elseif ($totalKey == 'weight') {
            $qiKey = 'row_weight';
        } elseif ($totalKey == 'total_qty') {
            $qiKey = 'qty';
        } else {
            return false;
        }
        $total = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            }
            else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem() && $quoteItem->getUdropshipVendor()==$vId) {
                $total = $quoteItem->getDataUsingMethod($qiKey);
            }
        }
        return $total;
    }
}