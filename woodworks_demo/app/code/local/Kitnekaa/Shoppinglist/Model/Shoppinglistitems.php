<?php
class Kitnekaa_Shoppinglist_Model_ShoppinglistItems extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shoppinglist/shoppinglistitems');
    }
}