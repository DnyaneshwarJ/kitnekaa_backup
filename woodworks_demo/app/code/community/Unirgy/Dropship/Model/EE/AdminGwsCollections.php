<?php

class Unirgy_Dropship_Model_EE_AdminGwsCollections extends Enterprise_AdminGws_Model_Collections
{
    public function addStoreAttributeToFilter($collection)
    {
        if ($collection->hasFlag('ee_gws_store_use_main')) {
            //$collection->getSelect()->where('main_table.store_id in (?)', $this->_role->getStoreIds());
            $collection->addFieldToFilter('main_table.store_id', array('in' => $this->_role->getStoreIds()));
        } else {
            parent::addStoreAttributeToFilter($collection);
        }
    }
}