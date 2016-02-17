<?php

class Unirgy_DropshipVendorProduct_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ?
            $this->_getClearSelect() :
            $this->_buildClearSelect($select);
        if ($this->getFlag('has_group_entity')) {
            $group = $countSelect->getPart(Zend_Db_Select::GROUP);
            $newGroup = array();
            foreach ($group as $g) {
                if ("$g"!='e.entity_id') {
                    $newGroup[] = $g;
                }
            }
            $countSelect->setPart(Zend_Db_Select::GROUP, $newGroup);
        }
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        return $countSelect;
    }
}