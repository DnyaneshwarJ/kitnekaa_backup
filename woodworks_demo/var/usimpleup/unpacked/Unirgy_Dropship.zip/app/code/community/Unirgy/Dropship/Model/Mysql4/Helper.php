<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Mysql4_Helper extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_setResource('udropship');
    }
    static $metaKey;
    static $transport;
    static protected function _metaKey($key)
    {
        self::_transport();
        return self::$metaKey.$key;
    }
    static protected function _setMetaData($key, $value)
    {
        self::_transport()->setData(self::_metaKey($key), $value);
    }
    static protected function _getMetaData($key)
    {
        return self::_transport()->getData(self::_metaKey($key));
    }
    static protected function _transport($init=false)
    {
        if (null === self::$transport) {
            self::$transport = new Varien_Object();
            self::$metaKey = md5(uniqid(microtime(), true));
        }
        if ($init) {
            self::$metaKey = md5(uniqid(microtime(), true));
            self::$transport->unsetData();
        }
        return self::$transport;
    }
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }
    public function getTable($table)
    {
        return strpos($table, '/') ? parent::getTable($table) : $table;
    }
    protected function _normalizeIdFieldWithValue($id, $res)
    {
        if ($id instanceof Zend_Db_Expr
            || $id instanceof Zend_Db_Select
        ) {
            $id = array($res->getIdFieldName()=>$id);
        } elseif ($id===false || is_array($id) && empty($id)) {
            $id = array($res->getIdFieldName()=>false);
        } elseif ($id===true) {
            $id = array($res->getIdFieldName()=>true);
        } elseif ($id===null) {
            $id = array($res->getIdFieldName()=>null);
        } else {
            if (!is_array($id)) {
                $id = array($id);
            }
            reset($id);
            if (is_numeric(key($id)) || count($id)>1) {
                $id = array($res->getIdFieldName()=>$id);
            }
        }
        return $id;
    }
    protected function _prepareResourceData($res, $id, $data, $fields=null)
    {
        $id = $this->_normalizeIdFieldWithValue($id, $res);
        if ($res instanceof Mage_Eav_Model_Entity_Abstract) {
            $table = $res->getEntityTable();
        } elseif (is_object($res)) {
            if (method_exists($res, 'getMainTable')) {
                $table = $res->getMainTable();
            } elseif (method_exists($res, '__toString')) {
                $table = $this->getTable($res->__toString());
            } else {
                Mage::throwException('object cannot be converted to table name string');
            }
        } elseif (is_array($res)) {
            Mage::throwException('array is not acceptable as table name string');
        } else {
            $table = $this->getTable($res);
        }
        if (is_array($fields)) {
            $data = array_intersect_key($data, array_flip($fields));
        }
        self::_transport()->setData($data);
        $preparedData = $this->_prepareDataForTable(self::_transport(), $table);
        if (is_array($fields)) {
            $preparedData = array_intersect_key($preparedData, array_flip($fields));
        }
        self::_setMetaData('id',    $id);
        self::_setMetaData('table', $table);
        self::_setMetaData('data',  $preparedData);
        self::_setMetaData('is_prepared',  true);
        return $this;
    }
    public function myPrepareDataForTable($table, $data, $full=false)
    {
        self::_transport(true);
        self::_transport()->setData($data);
        $result = $this->_myPrepareDataForTable(self::_transport(), $this->getTable($table), $full);
        if (!$full) {
            $result = array_intersect_key($result, $data);
        }
        self::_transport(true);
        return $result;
    }
    protected function _myPrepareDataForTable(Varien_Object $object, $table, $full=false)
    {
        $data = array();
        $fields = $this->_getWriteAdapter()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if ($object->hasData($field) || $full) {
                $fieldValue = $object->getData($field);
                if ($fieldValue instanceof Zend_Db_Expr) {
                    $data[$field] = $fieldValue;
                } else {
                    if (null !== $fieldValue) {
                        $data[$field] = $this->_prepareValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                    } elseif (!empty($fields[$field]['NULLABLE'])||!empty($fields[$field]['PRIMARY'])) {
                        $data[$field] = new Zend_Db_Expr('NULL');
                    } elseif (isset($fields[$field]['DEFAULT'])) {
                        if ($fields[$field]['DEFAULT'] == 'CURRENT_TIMESTAMP') {
                            $data[$field] = new Zend_Db_Expr('CURRENT_TIMESTAMP');
                        } else {
                            $data[$field] = $fields[$field]['DEFAULT'];
                        }
                    } else {
                        $data[$field] = '';
                    }
                }
            }
        }
        return $data;
    }

    public function updateModelFields(Mage_Core_Model_Abstract $model, $fields)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $model->getId(), $model->getData(), $fields);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    public function updateModelData($model, $data, $id=null)
    {
        if (is_string($model)) {
            $model = Mage::getModel($model);
        }
        if (null === $id) {
            $id = $model->getId();
        }
        if (!$model instanceof Mage_Core_Model_Abstract) {
            Mage::throwException('$model should be instance of Mage_Core_Model_Abstract');
        }
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $id, $data);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    public function updateTableData($table, $idFieldWithValue, $data)
    {
        self::_transport(true);
        $this->_prepareResourceData($table, $idFieldWithValue, $data);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    protected function _updateTableData()
    {
        if (!self::_getMetaData('is_prepared')) {
            Mage::throwException('Nothing prepared for update');
        }
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $condition = $this->_getWriteAdapter()->quoteInto($_idField.' in (?)', $_idValue);
        $this->_getWriteAdapter()->update($table, $preparedData, $condition);
        return $this;
    }

    public function insertIgnore($table, $data)
    {
        $table = $this->getTable($table);
        self::_transport(true)->setData($data);
        $preparedData = $this->_prepareDataForTable(self::_transport(), $table);
        $write = $this->_getWriteAdapter();
        if (!empty($preparedData)) {
        $write->query(sprintf(
            "insert ignore into %s (%s) values (%s)",
            $write->quoteIdentifier($table),
            implode(',', array_map(array($write, 'quoteIdentifier'), array_keys($preparedData))),
            $write->quote($preparedData)
        ));
        }
        self::_transport(true);
        return $this;
    }

    public function multiInsertIgnore($table, $multiData)
    {
        $table = $this->getTable($table);
        $write = $this->_getWriteAdapter();
        $preparedData = array();
        $preparedDataKeys = array();
        foreach ($multiData as $data) {
            self::_transport(true)->setData($data);
            $_preparedData = $this->_myPrepareDataForTable(self::_transport(), $table, true);
            if (empty($_preparedData)) continue;
            if (empty($preparedDataKeys)) {
                $preparedDataKeys = implode(',', array_map(array($write, 'quoteIdentifier'), array_keys($_preparedData)));
            }
            $preparedData[] = $write->quote($_preparedData);
        }
        if (!empty($preparedData)) {
            $multiInsert = sprintf(
                "insert ignore into %s (%s) values (%s)",
                $write->quoteIdentifier($table),
                $preparedDataKeys,
                implode('),(', $preparedData)
            );
            $write->query($multiInsert);
        }
        self::_transport(true);
        return $this;
    }

    public function loadModelField(Mage_Core_Model_Abstract $model, $field)
    {
        $data = $this->_loadModelFields($model, array($field), false);
        return @$data[$field];
    }
    public function loadModelFieldForUpdate(Mage_Core_Model_Abstract $model, $field)
    {
        $data = $this->_loadModelFields($model, array($field), true);
        return @$data[$field];
    }
    public function loadModelFields(Mage_Core_Model_Abstract $model, $fields)
    {
        return $this->_loadModelFields($model, $fields, false);
    }
    public function loadModelFieldsForUpdate(Mage_Core_Model_Abstract $model, $fields)
    {
        return $this->_loadModelFields($model, $fields, true);
    }
    protected function _loadModelFields(Mage_Core_Model_Abstract $model, $fields, $forUpdate=false)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $model->getId(), array_flip($fields), $fields);
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        $preparedFields = array_keys($preparedData);
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $condition = $this->_getWriteAdapter()->quoteInto($_idField.' in (?)', $_idValue);
        $loadSel = $this->_getWriteAdapter()->select()->from($table, $preparedFields)->where($condition);
        if ($forUpdate) {
            $loadSel->forUpdate(true);
        }
        $data = $this->_getWriteAdapter()->fetchRow($loadSel);
        self::_transport(true);
        return $data;
    }
    public function loadDbColumnsForUpdate(Mage_Core_Model_Abstract $model, $ids, $fields, $extraCondition='')
    {
        return $this->_loadDbColumns($model, $ids, $fields, $extraCondition, true);
    }
    public function loadDbColumns(Mage_Core_Model_Abstract $model, $ids, $fields, $extraCondition='')
    {
        return $this->_loadDbColumns($model, $ids, $fields, $extraCondition, false);
    }
    protected function _loadDbColumns(Mage_Core_Model_Abstract $model, $ids, $fields, $extraCondition='', $forUpdate=false)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $ids, array_flip($fields), $fields);
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        $preparedFields = array_keys($preparedData);
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $excludeIdField = false;
        if (!in_array($_idField, $preparedFields)) {
            $preparedFields[] = $_idField;
            $excludeIdField = true;
        }
        $condition = '1';
        if ($_idValue instanceof Zend_Db_Expr
            || $_idValue instanceof Zend_Db_Select
        ) {
            $condition .= $this->_getWriteAdapter()->quoteInto(" AND $_idField ?", $_idValue);
        } elseif ($_idValue===false) {
            $condition .= " AND false";
        } elseif ($_idValue===null) {
            $condition .= $this->_getWriteAdapter()->quoteInto(" AND $_idField IS NULL", $_idValue);
        } elseif ($_idValue === true) {
        } else {
            $condition .= $this->_getWriteAdapter()->quoteInto(" AND $_idField in (?)", $_idValue);
        }
        $loadSel = $this->_getWriteAdapter()->select()->from($table, $preparedFields);
        $loadSel->where($condition);
        if ($model->getData('__udload_order')) {
            $__uloArr = $model->getData('__udload_order');
            if (!is_array($__uloArr)) {
                $__uloArr = array($__uloArr);
            }
            foreach ($__uloArr as $__ulo) {
                $__order = str_replace('{{table}}', $table, (string)$__ulo);
                if ($__ulo instanceof Zend_Db_Expr) {
                    $__order = new Zend_Db_Expr($__order);
                }
                if ($__order) $loadSel->order($__order);
            }
        }
        if (!empty($extraCondition)) {
            $extraCondition = str_replace('{{table}}', $table, $extraCondition);
            $loadSel->where($extraCondition);
        }
        if ($forUpdate) {
            $loadSel->forUpdate(true);
        }
        $result = array();
        $data = $this->_getWriteAdapter()->fetchAll($loadSel);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $tmp) {
                $__idValue = $tmp[$_idField];
                $result[$__idValue] = $tmp;
                if ($excludeIdField) {
                    unset($result[$__idValue][$_idField]);
                }
            }
        }
        self::_transport(true);
        return $result;
    }
    public function multiInsertOnDuplicate($table, $data, $fields=array())
    {
        $table = $this->getTable($table);
        $preparedData = array();
        foreach ($data as $single) {
            self::_transport(true)->setData($single);
            $preparedData[] = $this->_prepareDataForTable(self::_transport(), $table);
        }
        $write = $this->_getWriteAdapter();
        $write->insertOnDuplicate($table, $preparedData, $fields);
        return $this;
    }
    public function getOrderItemPoInfo($order)
    {
        $conn = $this->_getReadAdapter();
        $itemIds = array();
        foreach ($order->getAllItems() as $item) {
            $itemIds[] = $item->getId();
        }
        if (Mage::helper('udropship')->isModuleActive('udpo')) {
            $select = $conn->select()
                ->from(array('oi'=>$this->getTable('sales/order_item')), array())
                ->join(array('poi'=>$this->getTable('udpo/po_item')), 'poi.order_item_id=oi.item_id', array())
                ->join(array('po'=>$this->getTable('udpo/po')), 'poi.parent_id=po.entity_id', array())
                ->where('oi.item_id in (?)', $itemIds);
            $select->columns(array('po.increment_id','oi.item_id','poi.qty','po.udropship_vendor', 'po.udropship_status'));
            $rows = $conn->fetchAll($select);
        } else {
            $select = $conn->select()
                ->from(array('oi'=>$this->getTable('sales/order_item')), array())
                ->join(array('poi'=>$this->getTable('sales/shipment_item')), 'poi.order_item_id=oi.item_id', array())
                ->join(array('po'=>$this->getTable('sales/shipment')), 'poi.parent_id=po.entity_id', array())
                ->where('oi.item_id in (?)', $itemIds);
            $select->columns(array('po.increment_id','oi.item_id','poi.qty','po.udropship_vendor', 'po.udropship_status'));
            $rows = $conn->fetchAll($select);
        }
        return $rows;
    }
    public function getProductVendors($pId, $source='both')
    {
        if (!is_array($pId)) {
            if ($pId instanceof Mage_Catalog_Model_Product) {
                $pId = $pId->getId();
            }
            $pId = array($pId);
        }
        $products = array();

        $read = $this->_getReadAdapter();

        if (in_array($source, array('both','attribute'))) {
            $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
            $table = $attr->getBackend()->getTable();
            $select = $read->select()
                ->from($table, array('entity_id','value'))
                ->where('attribute_id=?', $attr->getId())
                ->where('entity_id in (?)', $pId);
            $rows = $read->fetchAll($select);
            foreach ($rows as $row) {
                $products[$row['entity_id']] = array($row['value']=>$row['value']);
            }
        }
        if (in_array($source, array('both','table'))) {
            $select = $read->select()
                ->from($this->getTable('udropship/vendor_product'), array('product_id','vendor_id'))
                ->where('product_id in (?)', $pId);
            $rows = $read->fetchAll($select);
            foreach ($rows as $row) {
                $_pId = $row['product_id'];
                $_vId = $row['vendor_id'];
                if (empty($products[$_pId])) {
                    $products[$_pId] = array();
                }
                $products[$_pId] = $products[$_pId]+array($_vId=>$_vId);
            }
        }

        return $products;
    }

}