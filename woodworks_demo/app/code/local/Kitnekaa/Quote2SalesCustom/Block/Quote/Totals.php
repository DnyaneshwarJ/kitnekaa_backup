<?php
class Kitnekaa_Quote2SalesCustom_Block_Quote_Totals extends Bobcares_Quote2Sales_Block_Quote_Totals
{
    protected function formatPrice($price, $addBrackets = false)
    {
        return Mage::helper('core')->currency($price, true, false);//$this->helper("quote2sales")->formatPrice($price, $addBrackets);
    }
}
