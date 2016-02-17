<?php

class Unirgy_DropshipShippingClass_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('udshipclass/customer');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('class_id', 'class_name');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('class_id', 'class_name');
    }

    public function addSortOrder()
    {
        $this->_select->order('sort_order ASC');
        return $this;
    }

    protected function _afterLoad()
    {
        $items = $this->getColumnValues('class_id');
        if (!count($items)) {
            parent::_afterLoad();
            return;
        }

        $conn = $this->getConnection();

        $table = $this->getTable('udshipclass/customer_row');
        $select = $conn->select()->from($table)->where($table.'.class_id IN (?)', $items);
        if ($result = $conn->fetchAll($select)) {
            $regionData = $regionIds = array();
            if ($this->getFlag('load_region_labels')) {
                foreach ($result as $row) {
                    $regionIds = array_unique(array_merge($regionIds,explode(',',$row['region_id'])));
                }
                if (!empty($regionIds)) {
                    $rFilterKey = Mage::helper('udropship')->hasMageFeature('resource_1.6')
                        ? 'main_table.region_id' : 'region.region_id';
                    $regionCollection = Mage::getModel('directory/region')->getCollection()
                        ->addFieldToFilter($rFilterKey, array('in'=>$regionIds));
                    foreach ($regionCollection as $reg) {
                        $regionData[$reg->getId()] = $reg->getData();
                    }
                }
            }
            foreach ($result as $row) {
                $item = $this->getItemById($row['class_id']);
                if (!$item) continue;
                $rows = $item->getRows();
                if (!$rows) $rows = array();
                $row['region_data'] = array_intersect_key($regionData, array_flip(explode(',', $row['region_id'])));
                $regionNames = $regionCodes = array();
                foreach ($row['region_data'] as $rd) {
                    $regionNames[$rd['region_id']] = $rd['name'];
                    $regionCodes[$rd['region_id']] = $rd['code'];
                }
                $row['region_names'] = $regionNames;
                $row['region_codes'] = $regionCodes;
                $rows[] = $row;
                $item->setRows($rows);
            }
        }

        parent::_afterLoad();
    }
}
