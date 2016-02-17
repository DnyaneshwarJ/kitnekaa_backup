<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');

$eav->addAttribute('catalog_product', 'udprod_attributes_changed', array(
    'type' => 'text', 'backend' => 'eav/entity_attribute_backend_serialized',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Changed Data',
));

$eav->addAttribute('catalog_product', 'udprod_cfg_simples_added', array(
    'type' => 'text', 'backend' => 'eav/entity_attribute_backend_serialized',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Simples Added',
));

$eav->addAttribute('catalog_product', 'udprod_cfg_simples_removed', array(
    'type' => 'text', 'backend' => 'eav/entity_attribute_backend_serialized',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Simples Removed',
));

$eav->addAttribute('catalog_product', 'udprod_pending_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Pending Notified',
));
$eav->addAttribute('catalog_product', 'udprod_approved_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Approved Notified',
));
$eav->addAttribute('catalog_product', 'udprod_fix_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Fix Notified',
));

$eav->addAttribute('catalog_product', 'udprod_pending_admin_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Pending Notified',
));
$eav->addAttribute('catalog_product', 'udprod_approved_admin_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Approved Notified',
));
$eav->addAttribute('catalog_product', 'udprod_fix_admin_notified', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Fix Notified',
));

$eav->addAttribute('catalog_product', 'udprod_pending_notify', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Pending Notify',
));
$eav->addAttribute('catalog_product', 'udprod_approved_notify', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Approved Notify',
));
$eav->addAttribute('catalog_product', 'udprod_fix_notify', array(
    'type' => 'int',
    'visible' => 0, 'required' => 0,
    'label' => 'uMarketplace Fix Notify',
));

$eav->addAttribute('catalog_product', 'udprod_fix_description', array(
    'type' => 'text',
    'input'=>'textarea', 'label' => 'Fix Description',
    'user_defined' => 1, 'required' => 0, 'group'=>'Fixes Required From Vendor'
));

$installer->endSetup();
