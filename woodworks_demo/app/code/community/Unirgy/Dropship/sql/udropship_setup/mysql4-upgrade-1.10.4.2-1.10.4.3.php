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

$conn = $this->_conn;

if (Mage::helper('udropship')->isSalesFlat()) {
    $searchSelect = $conn->select()
        ->from(array('po' => $this->getTable('sales/shipment')), array('entity_id'))
        ->joinLeft(array('vs' => $this->getTable('udropship/vendor_statement')), 'vs.statement_id=po.statement_id', array())
        ->where("po.statement_id is not null and po.statement_id!='' and vs.statement_id is null");

    $updateSelect = $conn->select()
        ->join(array('_orp' => $searchSelect), '_orp.entity_id=_po.entity_id', array())
        ->columns(array('statement_id' => new Zend_Db_Expr('NULL')));

    $updateSql = $updateSelect->crossUpdateFromSelect(array('_po' => $this->getTable('sales/shipment')));
    //print $updateSql."\n\n\n";
    $conn->raw_query($updateSql);

    $updateSql = $updateSelect->crossUpdateFromSelect(array('_po' => $this->getTable('sales/shipment_grid')));
    //print $updateSql."\n\n\n";
    $conn->raw_query($updateSql);

} else {
    $eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
    $poAttr  = $eav->getAttribute('shipment', 'statement_id');
    $sET = $eav->getEntityType('shipment');

    $searchSelect = $conn->select()
        ->from(array('orp' => $this->getTable($sET['entity_table']).'_'.$poAttr['backend_type']), array('entity_id'))
        ->joinLeft(array('vs' => $this->getTable('udropship/vendor_statement')), 'vs.statement_id=orp.value', array())
        ->where("orp.value is not null and orp.value!='' and vs.statement_id is null")
        ->where('orp.attribute_id=?', $poAttr['attribute_id']);

    //print $searchSelect."\n\n\n";

    $poIds = $conn->fetchCol($searchSelect);

    //print_r($poIds);

    if (!empty($poIds)) {
        $conn->delete(
            $this->getTable($sET['entity_table']).'_'.$poAttr['backend_type'],
            $conn->quoteInto('entity_id in (?) ', $poIds)
            .$conn->quoteInto(' and attribute_id=?', $poAttr['attribute_id'])
        );
    }
}

//throw new Exception('test');

$this->endSetup();
