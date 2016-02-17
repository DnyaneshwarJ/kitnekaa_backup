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

abstract class Unirgy_Dropship_Model_Mysql4_Vendor_Statement_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    abstract public function initAdjustmentsCollection($statement);
    abstract protected function _getRowTable();
    abstract protected function _getAdjustmentTable();
    abstract protected function _cleanAdjustmentTable($statement);
    abstract protected function _cleanRowTable($statement);

    public function fixStatementDate($vendor, $poType, $stPoStatuses, $dateFrom=null, $dateTo=null)
    {
        $conn = $this->_getWriteAdapter();
        if ('po' == $poType) {
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('udpo/po'),
                $conn->select()
                    ->from(array('st' => $this->getTable('udpo/po')), array())
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
            );
            //Mage::helper('udropship')->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
            $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('udpo/po_grid'),
                $conn->select()
                    ->from(array('st' => $this->getTable('udpo/po_grid')), array())
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
            );
            //Mage::helper('udropship')->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
        } else {
            if (Mage::helper('udropship')->isSalesFlat()) {
                if (!is_array($stPoStatuses)) {
                    $stPoStatuses = explode(',', $stPoStatuses);
                }
                $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                    $this->getTable('sales/shipment'),
                    $conn->select()
                        ->from(array('st' => $this->getTable('sales/shipment')), array())
                        ->where('st.udropship_vendor=?', $vendor->getId())
                        ->where('st.udropship_status in (?)', $stPoStatuses)
                        ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                        ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                        ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                        ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
                );
                //Mage::helper('udropship')->dump($sdInsSelect, 'fixStatementDate');
                $conn->query($sdInsSelect);
                $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                    $this->getTable('sales/shipment_grid'),
                    $conn->select()
                        ->from(array('st' => $this->getTable('sales/shipment')), array())
                        ->join(array('stg' => $this->getTable('sales/shipment_grid')), 'stg.entity_id=st.entity_id', array())
                        ->where('st.udropship_vendor=?', $vendor->getId())
                        ->where('st.udropship_status in (?)', $stPoStatuses)
                        ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                        ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                        ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                        ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
                );
                //Mage::helper('udropship')->dump($sdInsSelect, 'fixStatementDate');
                $conn->query($sdInsSelect);
            } else {
                $eav = Mage::getSingleton('eav/config');
                $sdAttr = $eav->getAttribute('shipment', 'statement_date');
                $vAttr = $eav->getAttribute('shipment', 'udropship_vendor');
                $udsAttr = $eav->getAttribute('shipment', 'udropship_status');
                $sET    = $eav->getEntityType('shipment');

                $sTbl   = $this->getTable($sET->getData('entity_table'));
                $sdTbl  = $sTbl.'_'.$sdAttr->getData('backend_type');
                $vTbl   = $sTbl.'_'.$vAttr->getData('backend_type');
                $udsTbl = $sTbl.'_'.$udsAttr->getData('backend_type');
                if (!is_array($stPoStatuses)) {
                    $stPoStatuses = explode(',', $stPoStatuses);
                }
                $sdJoinCond = implode(' and ', array(
                    'sd.entity_id=st.entity_id',
                    $conn->quoteInto('sd.attribute_id=?', $sdAttr->getData('attribute_id')),
                    $conn->quoteInto('sd.entity_type_id=?', $sET->getData('entity_type_id')),
                ));

                $vJoinCond = implode(' and ', array(
                    'vt.entity_id=st.entity_id',
                    $conn->quoteInto('vt.attribute_id=?', $vAttr->getData('attribute_id')),
                    $conn->quoteInto('vt.entity_type_id=?', $sET->getData('entity_type_id')),
                ));

                $udsJoinCond = implode(' and ', array(
                    'udst.entity_id=st.entity_id',
                    $conn->quoteInto('udst.attribute_id=?', $udsAttr->getData('attribute_id')),
                    $conn->quoteInto('udst.entity_type_id=?', $sET->getData('entity_type_id')),
                ));
                $sdInsSelect = sprintf("INSERT IGNORE INTO %s (entity_type_id,attribute_id,entity_id,value) %s",
                    $sdTbl,
                    $conn->select()
                        ->from(array('st' => $sTbl), array())
                        ->join(array('vt' => $vTbl), $vJoinCond, array())
                        ->join(array('udst' => $udsTbl), $udsJoinCond, array())
                        ->joinLeft(array('sd' => $sdTbl), $sdJoinCond, array())
                        ->where('vt.value=?', $vendor->getId())
                        ->where('udst.value in (?)', $stPoStatuses)
                        ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                        ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                        ->where("sd.value is null or sd.value='0000-00-00 00:00:00'")
                        ->columns(array(new Zend_Db_Expr($sET->getData('entity_type_id')), new Zend_Db_Expr($sdAttr->getData('attribute_id')), 'entity_id', 'st.created_at'))
                );
                //Mage::helper('udropship')->dump($sdInsSelect, 'fixStatementDate');
                $conn->query($sdInsSelect);
            }
        }
    }

    protected function _prepareRowSave($statement, $row)
    {
        $row['row_json'] = Zend_Json::encode($row);
        $row = array_merge($row, $row['amounts']);
        return $row;
    }
    protected function _prepareAdjustmentSave($statement, $adjustment)
    {
        $adjustment['adjustment_prefix'] = isset($adjustment['forced_adjustment_prefix'])
            ? $adjustment['forced_adjustment_prefix']
            : $statement->getAdjustmentPrefix();
        return $adjustment;
    }
    
    protected $_tableColumns = array();
    protected function _initTableColumns($table)
    {
        if (!isset($this->_tableColumns[$table])) {
            $_columns = $this->_getWriteAdapter()->describeTable($table);
            $this->_tableColumns[$table] = array();
            foreach ($_columns as $_k => $_c) {
                if (!$_c['IDENTITY']) $this->_tableColumns[$table][$_k] = $_c;
            }
        }
        return $this;
    }
    public function getTableColumns($table, $returnKeys=true)
    {
        $this->_initTableColumns($table);
        return $returnKeys
            ? array_keys($this->_tableColumns[$table])
            : $this->_tableColumns[$table];
    }
    protected function _prepareTableInsert($table, $data, $returnSql=true)
    {
        $this->_initTableColumns($table);
        $row = array();
        foreach ($this->_tableColumns[$table] as $key => $column) {
            if (isset($data[$key])) {
                $row[] = $this->_prepareValueForSave($data[$key], $column['DATA_TYPE']);
            } else if ($column['NULLABLE']) {
                $row[] = new Zend_Db_Expr('NULL');
            } elseif (isset($column['DEFAULT'])) {
                if ($column['DEFAULT'] == 'CURRENT_TIMESTAMP') {
                    $row[] = new Zend_Db_Expr('CURRENT_TIMESTAMP');
                } else {
                    $row[] = $column['DEFAULT'];
                }
            } else {
                $row[] = '';
            }
        }
        return $returnSql 
            ? implode(',', array_map(array($this->_getWriteAdapter(), 'quote'), $row))
            : $row;
    }
    
    protected function _saveRows(Mage_Core_Model_Abstract $object)
    {
        $this->_cleanRowTable($object);
        if ($object->getOrders()) {
            $rows = array();
            $rawRows = array();
            foreach ($object->getOrders() as $order) {
                $_row = $this->_prepareTableInsert($this->_getRowTable(), $this->_prepareRowSave($object, $order), false);
                foreach ($_row as $_r) {
                    $rawRows[] = $_r;
                }
                $rows[] = implode(',', array_fill(0, count($_row), '?'));
            }
            $this->_getWriteAdapter()->query(sprintf(
                'INSERT INTO %s (%s) VALUES (%s) %s',
                $this->_getRowTable(), implode(',', $this->getTableColumns($this->_getRowTable())), implode('),(', $rows),
                Mage::helper('udropship')->createOnDuplicateExpr($this->_getWriteAdapter(), $this->getTableColumns($this->_getRowTable()))
            ), $rawRows);
        }
        return $this;
    }

    protected function _saveAdjustments(Mage_Core_Model_Abstract $object)
    {
        $this->_cleanAdjustmentTable($object);
        $adjRows = array();
        foreach ($object->getAdjustmentsCollection() as $adjustment) {
            $adjRows[] = $this->_prepareTableInsert($this->_getAdjustmentTable(), $this->_prepareAdjustmentSave($object, $adjustment->getData()));
        }
        $object->resetAdjustmentCollection();
        if ($object->getOrders()) {
            foreach ($object->getOrders() as $order) {
                foreach ($order['adjustments'] as $adj) {
                    $adjRows[] = $this->_prepareTableInsert($this->_getAdjustmentTable(), $this->_prepareAdjustmentSave($object, $adj));
                }
            }
        }
        if (!empty($adjRows)) {
            $this->_getWriteAdapter()->query(sprintf(
                'INSERT INTO %s (%s) VALUES (%s) %s',
                $this->_getAdjustmentTable(), implode(',', $this->getTableColumns($this->_getAdjustmentTable())), implode('),(', $adjRows),
                Mage::helper('udropship')->createOnDuplicateExpr($this->_getWriteAdapter(), $this->getTableColumns($this->_getAdjustmentTable()))
            ));
            $this->_getWriteAdapter()->update(
                $this->_getAdjustmentTable(), 
                array('adjustment_id' => new Zend_Db_Expr('concat(adjustment_prefix, id)')),
                'adjustment_id is null'
            );
        }
        return $this;
    }
    
    protected function _cleanStatement(Mage_Core_Model_Abstract $object)
    {
        if ($object->getOrders()) {
            $this->_changePosAttribute(array_keys($object->getOrders()), $object->getPoType(), 'udropship_payout_status', NULL, $this->_getCleanExcludePoSelect($object));
        }
        $this->_cleanAdjustmentTable($object);
        return $this;
    }
    protected function _changePosAttribute($poIds, $poType, $poAttr, $poAttrValue, $excludePoSelect=null)
    {
        if (empty($poIds)) return $this;
        $conn = $this->_getWriteAdapter();
        if (!is_null($excludePoSelect)) {
            if (Mage::helper('udropship')->isSalesFlat()) {
                $_sTbl = $this->getTable('sales/shipment');
            } else {
                $sEt    = Mage::getSingleton('eav/config')->getEntityType('shipment');
                $_sTbl = $this->getTable($sEt->getEntityTable());
            }
            $poIds = $conn->fetchCol(
                $conn->select()
                    ->from($poType == 'po' ? $this->getTable('udpo/po') : $_sTbl, array('entity_id'))
                    ->where('entity_id in (?)', $poIds)
                    ->where('entity_id not in (?)', $excludePoSelect)
            );
        }
        if (Mage::helper('udropship')->isSalesFlat()) {
            $conn->update(
                $poType == 'po' ? $this->getTable('udpo/po') : $this->getTable('sales/shipment'), 
                array($poAttr=>$poAttrValue),
                $conn->quoteInto('entity_id in (?)', $poIds)
            );
            $conn->update(
                $poType == 'po' ? $this->getTable('udpo/po_grid') : $this->getTable('sales/shipment_grid'),
                array($poAttr=>$poAttrValue),
                $conn->quoteInto('entity_id in (?)', $poIds)
            );
            if (Mage::helper('udropship')->isUdpoActive()) {
                if ($poType == 'po') {
                    $poCompIds = $conn->fetchCol(
                        $conn->select()
                            ->from($this->getTable('sales/shipment'), array('entity_id'))
                            ->where('udpo_id in (?)', $poIds)
                    );
                    $conn->update(
                        $this->getTable('sales/shipment'), 
                        array($poAttr=>$poAttrValue),
                        $conn->quoteInto('entity_id in (?)', $poCompIds)
                    );
                    $conn->update(
                        $this->getTable('sales/shipment_grid'),
                        array($poAttr=>$poAttrValue),
                        $conn->quoteInto('entity_id in (?)', $poCompIds)
                    );
                } else {
                    $poCompIds = $conn->fetchCol(
                        $conn->select()
                            ->from($this->getTable('sales/shipment'), array('udpo_id'))
                            ->where('entity_id in (?)', $poIds)
                    );
                    $conn->update(
                        $this->getTable('udpo/po'), 
                        array($poAttr=>$poAttrValue),
                        $conn->quoteInto('entity_id in (?)', $poCompIds)
                    );
                    $conn->update(
                        $this->getTable('udpo/po_grid'),
                        array($poAttr=>$poAttrValue),
                        $conn->quoteInto('entity_id in (?)', $poCompIds)
                    );
                }
            }
        } else {
            $sEt    = Mage::getSingleton('eav/config')->getEntityType('shipment');
            $psAttr = Mage::getSingleton('eav/config')->getAttribute('shipment', $poAttr);
            if ($poAttrValue === NULL) {
                $conn->delete(
                    $psAttr->getBackendTable(),
                    $conn->quoteInto('entity_id in (?) ', $poIds)
                    .$conn->quoteInto(' and attribute_id=?', $psAttr->getId())
                );
            } else {
                $iRows = array();
                foreach ($poIds as $poId) {
                    $iRows[] = $conn->quoteInto('?', array(
                        'entity_type_id' => $sEt->getId(),
                        'attribute_id' => $psAttr->getId(),
                        'entity_id' => $poId,
                        'value' => $poAttrValue
                    ));
                }
                $columns = array('entity_type_id','attribute_id','entity_id','value');
                $conn->query(sprintf(
                    'INSERT INTO %s (%s) VALUES (%s) %s',
                    $psAttr->getBackendTable(), implode(',', $columns), implode('),(', $iRows), 
                    Mage::helper('udropship')->createOnDuplicateExpr($conn, $columns) 
                ));
            }
        }
        return $this;
    }
}
