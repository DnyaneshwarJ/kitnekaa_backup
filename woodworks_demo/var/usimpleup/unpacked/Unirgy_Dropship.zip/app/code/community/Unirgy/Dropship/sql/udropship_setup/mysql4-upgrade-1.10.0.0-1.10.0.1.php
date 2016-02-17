<?php

$this->startSetup();

$conn = $this->_conn;

$table = $this->getTable('udropship_vendor_statement_row');
$conn->dropForeignKey($table, 'FK_udropship_vendor_statement_row');
$conn->addConstraint('FK_udropship_vendor_statement_row', $table, 'statement_id', $this->getTable('udropship_vendor_statement'), 'vendor_statement_id', 'CASCADE', 'CASCADE');

$this->endSetup();