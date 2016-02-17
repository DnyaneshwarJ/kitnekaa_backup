<?php

class Unirgy_DropshipShippingClass_Model_Mysql4_Vendor extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('udshipclass/vendor', 'class_id');
    }

    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('class_name'),
            'title' => Mage::helper('udropship')->__('An error occurred while saving this ship class. A class with the same name already exists.'),
        ));
        return $this;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            return parent::_afterLoad($object);
        }

        $conn = $this->_getReadAdapter();

        $table = $this->getTable('udshipclass/vendor_row');
        $select = $conn->select()->from($table)->where($table.'.class_id IN (?)', $object->getId());
        if ($result = $conn->fetchAll($select)) {
            $regionData = $regionIds = array();
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
            foreach ($result as $row) {
                $rows = $object->getRows();
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
                $object->setRows($rows);
            }
        }

        return parent::_afterLoad($object);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $conn = $this->_getWriteAdapter();
        $conn->delete(
            $this->getTable('udshipclass/vendor_row'),
            $conn->quoteInto('class_id=?', $object->getId())
        );
        if (($rows = $object->getRows()) && is_array($rows)) {
            unset($rows['$ROW']);
            foreach ($rows as &$row) {
                if (empty($row['region_id'])) {
                    $row['region_id'] = '';
                }
                if (is_array($row['region_id'])) {
                    $row['region_id'] = implode(',',$row['region_id']);
                }
                $row['class_id'] = $object->getId();
            }
            unset($row);
            if (!empty($rows)) {
            Mage::getResourceSingleton('udropship/helper')->multiInsertOnDuplicate(
                $this->getTable('udshipclass/vendor_row'), $rows, array('postcode','region_id')
            );
            }
        }
    }
}
