<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor'), 'tiercom_fixed_calc_type', "varchar(128) default ''");

$installer->endSetup();
