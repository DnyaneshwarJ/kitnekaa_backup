<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('salesrule/rule'), 'udropship_vendor', 'int(11)');

$installer->endSetup();
