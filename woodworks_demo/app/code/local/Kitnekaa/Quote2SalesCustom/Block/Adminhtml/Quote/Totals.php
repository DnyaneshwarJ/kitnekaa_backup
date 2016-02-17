<?php

/**
 * Quote Block for Viewing a quote's totals
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Quote_Totals extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Totals
{

    /*
     * Displays the prices of each total
     */
    public function displayPrices($quote, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        $helper = Mage::helper("quote2sales");

        if ($quote && $helper->isCurrencyDifferent()) {
            $res = '<strong>';
            $res .= $helper->formatBasePrice($basePrice);
            $res .= '</strong>' . $separator;
            $res .= '[' . Mage::helper('core')->currency($price, true, false) . ']';
        } elseif ($quote) {
            $res = Mage::helper('core')->currency($price, true, false);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        } else {
            $res = Mage::app()->getStore()->formatPrice($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
    }

}