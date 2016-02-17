<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$eav->updateAttribute('catalog_product', 'udprod_attributes_changed', array(
    'is_required' => 0,
    'is_visible' => 0
));

$eav->updateAttribute('catalog_product', 'udprod_cfg_simples_added', array(
    'is_required' => 0,
    'is_visible' => 0
));

$eav->updateAttribute('catalog_product', 'udprod_cfg_simples_removed', array(
    'is_required' => 0,
    'is_visible' => 0
));


$installer->endSetup();
