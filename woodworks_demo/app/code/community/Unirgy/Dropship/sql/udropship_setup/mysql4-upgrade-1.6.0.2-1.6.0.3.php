<?php

$this->startSetup();

$c = $this->_conn;

$c->addColumn($this->getTable('udropship/vendor_shipping'), 'carrier_code', 'varchar(50)');

do {
    $dups = $c->fetchAll("SELECT MIN(vendor_product_id) min_pk, vendor_id, product_id, COUNT(*) cnt FROM {$this->getTable('udropship/vendor_product')} GROUP BY vendor_id, product_id HAVING cnt>1");
    if (!$dups) {
        break;
    }
    $ids = array();
    foreach ($dups as $r) {
        $ids[] = $r['min_pk'];
    }
    $c->delete($this->getTable('udropship/vendor_product'), $c->quoteInto('vendor_product_id in (?)', $ids));
} while (true);


$c->addKey($this->getTable('udropship/vendor_product'), 'IDX_vendor_product_unique', array('vendor_id', 'product_id'), 'unique');

$this->endSetup();