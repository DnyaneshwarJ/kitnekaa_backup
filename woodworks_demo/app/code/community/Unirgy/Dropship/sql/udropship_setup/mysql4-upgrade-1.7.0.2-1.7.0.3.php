<?php

$this->startSetup();

$c = $this->_conn;

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$cEav->updateAttribute('catalog_product', 'udropship_vendor', 'is_required', 0);

$c->addColumn($this->getTable('udropship/vendor'), 'fax', 'varchar(50) after telephone');

$c->addColumn($this->getTable('udropship/vendor_shipping'), 'carrier_code', 'varchar(50)');
$c->addColumn($this->getTable('udropship/vendor_shipping'), 'est_carrier_code', 'varchar(50)');

$this->endSetup();