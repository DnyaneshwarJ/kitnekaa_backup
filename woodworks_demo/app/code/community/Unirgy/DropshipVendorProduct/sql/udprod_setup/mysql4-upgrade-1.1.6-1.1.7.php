<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->modifyColumn($this->getTable('core/config_data'), 'value', 'MEDIUMTEXT');

$installer->endSetup();
