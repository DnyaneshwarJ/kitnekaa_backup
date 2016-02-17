<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor'), 'vacation_mode', 'tinyint');
$conn->addColumn($this->getTable('udropship/vendor'), 'vacation_end', 'datetime');
$conn->addColumn($this->getTable('udropship/vendor'), 'vacation_message', 'varchar(255)');

$installer->endSetup();
