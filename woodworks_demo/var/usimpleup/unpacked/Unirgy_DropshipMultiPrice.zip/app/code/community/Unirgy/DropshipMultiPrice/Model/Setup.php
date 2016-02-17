<?php

class Unirgy_DropshipMultiPrice_Model_Setup extends Mage_Sales_Model_Mysql4_Setup
{
    public function syncIndexTables()
    {
        $canStates = Mage::getSingleton('udmultiprice/source')
            ->setPath('vendor_product_state_canonic')
            ->toOptionHash();
        foreach ($canStates as $csKey=>$csLbl) {
            foreach (array(
                $this->getTable('catalog/product_index_price'),
                $this->getTable('catalog/product_index_price').'_idx',
                $this->getTable('catalog/product_index_price').'_tmp',
                $this->getTable('catalog/product_price_indexer_final_idx'),
                $this->getTable('catalog/product_price_indexer_final_tmp'),
            ) as $tbl) {
                $this->_conn->addColumn($tbl, 'udmp_'.$csKey.'_min_price', 'decimal(12,4)');
                $this->_conn->addColumn($tbl, 'udmp_'.$csKey.'_max_price', 'decimal(12,4)');
                $this->_conn->addColumn($tbl, 'udmp_'.$csKey.'_cnt', 'int(10)');
            }
        }
    }

}