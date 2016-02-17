<?php

$this->startSetup();

$c = $this->_conn;

$c->addColumn($this->getTable('udropship/vendor_shipping'), 'carrier_code', 'varchar(50)');
$c->addColumn($this->getTable('udropship/vendor_shipping'), 'est_carrier_code', 'varchar(50)');

$this->endSetup();