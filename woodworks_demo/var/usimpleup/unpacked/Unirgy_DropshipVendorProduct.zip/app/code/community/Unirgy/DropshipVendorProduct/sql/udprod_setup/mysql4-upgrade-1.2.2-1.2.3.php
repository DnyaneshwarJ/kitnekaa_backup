<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$eav->updateAttribute('catalog_product', 'udprod_attributes_changed', array(
    'backend_model' => 'eav/entity_attribute_backend_serialized',
    'source_model' => new Zend_Db_Expr('null')
));

$eav->updateAttribute('catalog_product', 'udprod_cfg_simples_added', array(
    'backend_model' => 'eav/entity_attribute_backend_serialized',
    'source_model' => new Zend_Db_Expr('null')
));

$eav->updateAttribute('catalog_product', 'udprod_cfg_simples_removed', array(
    'backend_model' => 'eav/entity_attribute_backend_serialized',
    'source_model' => new Zend_Db_Expr('null')
));


$installer->endSetup();
