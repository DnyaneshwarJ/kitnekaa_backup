<?php
class Kitnekaa_Shoppinglist_Helper_Wishlist extends Mage_Wishlist_Helper_Data
{
    public function isAllowInCart()
    {
        return false;
    }
}