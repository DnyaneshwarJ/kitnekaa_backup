<?php
class Kitnekaa_Shoppinglist_Model_Resource_Shoppinglistitems extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        // parent::_construct();
        $this->_init('shoppinglist/shoppinglist_items','id');
    }
}