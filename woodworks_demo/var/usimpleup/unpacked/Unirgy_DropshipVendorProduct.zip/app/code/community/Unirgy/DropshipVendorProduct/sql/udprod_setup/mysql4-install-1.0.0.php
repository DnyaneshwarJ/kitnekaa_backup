<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor'), 'udprod_template_sku', 'text');

$conn->addColumn($this->getTable('catalog/product_attribute_media_gallery'), 'super_attribute', 'varchar(255)');
$conn->addColumn($this->getTable('catalog/product_super_attribute'), 'identify_image', 'tinyint(1) default 1');

$installer->endSetup();
