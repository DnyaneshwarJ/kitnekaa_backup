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

$this->startSetup();

if (Mage::helper('udropship')->isSalesFlat()) {
    $this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'statement_date', 'datetime');
    $this->_conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'statement_date', 'datetime');
    $this->_conn->addKey($this->getTable('sales_flat_shipment_grid'), 'IDX_UDROPSHIP_STATEMENT_DATE', 'statement_date');

    $vendors = Mage::getResourceModel('udropship/vendor_collection');
    foreach ($vendors as $vendor) {
        $vendor->afterLoad();
        if ('shipment' == $vendor->getStatementPoType()) {
            $stPoStatuses = $vendor->getStatementPoStatus();
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('sales_flat_shipment'),
                $this->_conn->select()
                    ->from(array('st' => $this->getTable('sales_flat_shipment')), array())
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
            );
            $this->_conn->query($sdInsSelect);
            $sdInsSelect = sprintf("INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('sales_flat_shipment_grid'),
                $this->_conn->select()
                    ->from(array('st' => $this->getTable('sales_flat_shipment')), array())
                    ->join(array('stg' => $this->getTable('sales_flat_shipment_grid')), 'stg.entity_id=st.entity_id', array())
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->columns(array('entity_id', 'statement_date' => 'st.created_at'))
            );
            $this->_conn->query($sdInsSelect);
        }
    }

} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

    $eav->addAttribute('shipment', 'statement_date', array('type' => 'datetime'));

    $sdAttr = $eav->getAttribute('shipment', 'statement_date');
    $vAttr = $eav->getAttribute('shipment', 'udropship_vendor');
    $udsAttr = $eav->getAttribute('shipment', 'udropship_status');
    $sET    = $eav->getEntityType('shipment');

    $sTbl   = $this->getTable($sET['entity_table']);
    $sdTbl  = $sTbl.'_'.$sdAttr['backend_type'];
    $vTbl   = $sTbl.'_'.$vAttr['backend_type'];
    $udsTbl = $sTbl.'_'.$udsAttr['backend_type'];

    $vJoinCond = implode(' and ', array(
        'vt.entity_id=st.entity_id',
        $this->_conn->quoteInto('vt.attribute_id=?', $vAttr['attribute_id']),
        $this->_conn->quoteInto('vt.entity_type_id=?', $sET['entity_type_id']),
    ));
    
    $udsJoinCond = implode(' and ', array(
        'udst.entity_id=st.entity_id',
        $this->_conn->quoteInto('udst.attribute_id=?', $udsAttr['attribute_id']),
        $this->_conn->quoteInto('udst.entity_type_id=?', $sET['entity_type_id']),
    ));

    $vendors = Mage::getResourceModel('udropship/vendor_collection');
    foreach ($vendors as $vendor) {
        $vendor->afterLoad();
        if ('shipment' == $vendor->getStatementPoType()) {
            $stPoStatuses = $vendor->getStatementPoStatus();
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            $sdInsSelect = sprintf("INSERT IGNORE INTO %s (entity_type_id,attribute_id,entity_id,value) %s",
                $sdTbl,
                $this->_conn->select()
                    ->from(array('st' => $sTbl), array())
                    ->join(array('vt' => $vTbl), $vJoinCond, array())
                    ->join(array('udst' => $udsTbl), $udsJoinCond, array())
                    ->where('vt.value=?', $vendor->getId())
                    ->where('udst.value in (?)', $stPoStatuses)
                    ->columns(array(new Zend_Db_Expr($sET['entity_type_id']), new Zend_Db_Expr($sdAttr['attribute_id']), 'entity_id', 'st.created_at'))
            );
            $this->_conn->query($sdInsSelect);
        }
    }
}

$this->_conn->addColumn($this->getTable('udropship/vendor_statement_row'), 'po_statement_date', 'datetime');

$this->endSetup();
