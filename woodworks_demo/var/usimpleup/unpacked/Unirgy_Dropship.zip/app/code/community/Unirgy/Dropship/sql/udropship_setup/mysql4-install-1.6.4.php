<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_shipping')}` (
`shipping_id` int(10) unsigned NOT NULL auto_increment,
`shipping_code` varchar(30) NOT NULL,
`shipping_title` varchar(100) NOT NULL,
`days_in_transit` varchar(20) NOT NULL,
PRIMARY KEY  (`shipping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_shipping_method')}` (
`shipping_id` int(10) unsigned NOT NULL,
`carrier_code` varchar(30) NOT NULL,
`method_code` varchar(30) NOT NULL,
KEY `FK_udropship_shipping_method` (`shipping_id`),
CONSTRAINT `FK_udropship_shipping_method` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_shipping_website')}` (
`shipping_id` int(10) unsigned NOT NULL,
`website_id` smallint(5) unsigned NOT NULL,
UNIQUE KEY `website_id` (`website_id`,`shipping_id`),
KEY `FK_udropship_shipping_website` (`shipping_id`),
CONSTRAINT `FK_udropship_shipping_website` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_shipping_website_pk` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor')}` (
`vendor_id` int(11) unsigned NOT NULL auto_increment,
`vendor_name` varchar(255) NOT NULL,
`vendor_attn` varchar(255) NOT NULL,
`email` varchar(127) NOT NULL,
`street` varchar(255) NOT NULL,
`city` varchar(50) NOT NULL,
`zip` varchar(20) default NULL,
`country_id` char(2) NOT NULL,
`region_id` mediumint(8) unsigned default NULL,
`region` varchar(50) default NULL,
`telephone` varchar(50) default NULL,
`status` char(1) NOT NULL,
`password` varchar(50) default NULL,
`password_hash` varchar(100) default NULL,
`password_enc` varchar(100) default NULL,
`carrier_code` varchar(50) default NULL,
`notify_new_order` tinyint not null default 1,
`label_type` varchar(10) not null default 'PDF',
`test_mode` tinyint(1) not null default 0,
`handling_fee` decimal(12,5) not null,
`ups_shipper_number` varchar(6),
`custom_data_combined` text,
`custom_vars_combined` text,
`email_template` int(7) unsigned,
`url_key` varchar(64),
`random_hash` varchar(64),
`created_at` timestamp,
PRIMARY KEY  (`vendor_id`),
KEY `IDX_STATUS` (`status`),
KEY `IDX_HASH` (`random_hash`),
KEY `IDX_CREATED` (`created_at`),
UNIQUE `IDX_URL_KEY` (`url_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_product')}` (
`vendor_product_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(10) unsigned NOT NULL,
`product_id` int(10) unsigned NOT NULL,
`priority` smallint(5) unsigned NOT NULL,
`carrier_code` varchar(50) default NULL,
`vendor_sku` varchar(64) default NULL,
`vendor_cost` decimal(12,4),
`stock_qty` decimal(12,4),
PRIMARY KEY  (`vendor_product_id`),
UNIQUE `IDX_vendor_product_unique` (`vendor_id`, `product_id`),
KEY `FK_udropship_vendor_product` (`vendor_id`),
KEY `FK_udropship_vendor_product_entity` (`product_id`),
CONSTRAINT `FK_udropship_vendor_product` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_vendor_product_entity` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_shipping')}` (
`vendor_shipping_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`shipping_id` int(10) unsigned NOT NULL,
`account_id` varchar(50) default NULL,
`price_type` tinyint(4) default NULL,
`price` decimal(12,4) default NULL,
`priority` smallint(5) unsigned NOT NULL,
`handling_fee` decimal(12,4) NOT NULL,
`carrier_code` varchar(50) NULL,
`est_carrier_code` varchar(50) NULL,
PRIMARY KEY  (`vendor_shipping_id`),
UNIQUE `IDX_VENDOR_SHIPPING` (`vendor_id`, `shipping_id`),
KEY `FK_udropship_vendor_shipping_vendor` (`vendor_id`),
KEY `FK_udropship_vendor_shipping` (`shipping_id`),
CONSTRAINT `FK_udropship_vendor_shipping` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_vendor_shipping_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_label_batch')}` (
batch_id int unsigned not null auto_increment primary key,
title varchar(255) not null,
label_type varchar(10) not null default 'PDF',
created_at datetime not null,
vendor_id int unsigned not null,
username varchar(50) not null,
shipment_cnt mediumint unsigned not null,
key(vendor_id),
key(created_at)
) engine=innodb default charset=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_label_shipment')}` (
batch_id int unsigned not null,
order_id int unsigned not null,
shipment_id int unsigned not null,
unique(batch_id, order_id, shipment_id),
key(order_id),
key(shipment_id)
) engine=innodb default charset=utf8;

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_statement')}` (
`vendor_statement_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(10) unsigned NOT NULL,
`statement_id` varchar(30) NOT NULL,
`statement_filename` varchar(128) not null,
`statement_period` varchar(20) not null,
`order_date_from` datetime not null,
`order_date_to` datetime not null,
`total_orders` mediumint not null,
`total_payout` decimal(12,4) not null,
`created_at` datetime not null,
`orders_data` longtext not null,
`email_sent` tinyint not null,
PRIMARY KEY  (`vendor_statement_id`),
KEY `FK_udropship_vendor_statement` (`vendor_id`),
KEY `IDX_statement_period` (`statement_period`),
KEY `IDX_vendor_id` (`vendor_id`),
KEY `IDX_email_sent` (`email_sent`),
UNIQUE `IDX_statement_id` (`statement_id`),
CONSTRAINT `FK_udropship_vendor_statement` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$cEav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$cEav->addAttribute('catalog_product', 'udropship_vendor', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Dropship Vendor',
    'group' => 'General',
    'global' => 2,
    'user_defined' => 1,
    'required' => 0,
    'visible' => 1,
    'backend' => 'Unirgy_Dropship_Model_Mysql4_Vendor_Backend',#'udropship_mysql4/vendor_backend',
    'source' => 'Unirgy_Dropship_Model_Vendor_Source',#'udropship/vendor_source',
));

if (version_compare(Mage::getVersion(), '1.3.0', '>=')) {
    $cEav->updateAttribute('catalog_product', 'udropship_vendor', 'used_in_product_listing', 1);
}
if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
    $cEav->updateAttribute('catalog_product', 'udropship_vendor', 'is_used_for_price_rules', 1);
}

$w = $this->_conn;

$w->addColumn($this->getTable('sales_flat_quote_address'), 'udropship_shipping_details', 'mediumtext');
$w->addColumn($this->getTable('sales_flat_quote_item'), 'udropship_vendor', 'int unsigned');
$w->addColumn($this->getTable('sales_flat_quote_address_item'), 'udropship_vendor', 'int unsigned');
$w->addColumn($this->getTable('sales_flat_order_item'), 'udropship_vendor', 'int unsigned');

if (Mage::helper('udropship')->isSalesFlat()) {
    $w->addColumn($this->getTable('sales_flat_order'), 'udropship_status', 'tinyint not null');
    try {
    $w->addKey($this->getTable('sales_flat_order'), 'udropship_status', 'udropship_status', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }

    $w->addColumn($this->getTable('sales_flat_order'), 'udropship_shipping_details', 'mediumtext');

    $table = $this->getTable('sales_flat_shipment');
    $w->addColumn($table, 'udropship_vendor', 'int');
    try {
    $w->addKey($table, 'udropship_vendor', 'udropship_vendor', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }
    $w->addColumn($table, 'udropship_status', 'int');
    try {
    $w->addKey($table, 'udropship_status', 'udropship_status', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }
    $w->addColumn($table, 'base_total_value', 'decimal(12,4)');
    $w->addColumn($table, 'total_value', 'decimal(12,4)');
    $w->addColumn($table, 'base_shipping_amount', 'decimal(12,4)');
    $w->addColumn($table, 'shipping_amount', 'decimal(12,4)');
    $w->addColumn($table, 'udropship_available_at', 'datetime');
    $w->addColumn($table, 'udropship_method', 'varchar(100)');
    $w->addColumn($table, 'udropship_method_description', 'text');
    $w->addColumn($table, 'base_tax_amount', 'decimal(12,4)');
    $w->addColumn($table, 'total_cost', 'decimal(12,4)');
    $w->addColumn($table, 'transaction_fee', 'decimal(12,4)');
    $w->addColumn($table, 'commission_percent', 'decimal(12,4)');
    $w->addColumn($table, 'handling_fee', 'decimal(12,4)');
    $w->addColumn($table, 'udropship_shipcheck', 'varchar(5)');
    try {
    $w->addKey($table, 'udropship_shipcheck', 'udropship_shipcheck', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }
    $w->addColumn($table, 'udropship_vendor_order_id', 'varchar(30)');

    $w->addColumn($this->getTable('sales_flat_shipment_item'), 'qty_shipped', 'decimal(12,4)');

    $table = $this->getTable('sales_flat_shipment_track');
    $w->addColumn($table, 'batch_id', 'int');
    try {
    $w->addKey($table, 'batch_id', 'batch_id', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }
    $w->addColumn($table, 'label_image', 'text');
    $w->addColumn($table, 'label_format', 'varchar(10)');
    $w->addColumn($table, 'label_pic', 'varchar(255)');
    $w->addColumn($table, 'final_price', 'decimal(12,4)');
    $w->addColumn($table, 'value', 'decimal(12,4)');
    $w->addColumn($table, 'length', 'decimal(12,4)');
    $w->addColumn($table, 'width', 'decimal(12,4)');
    $w->addColumn($table, 'height', 'decimal(12,4)');
    $w->addColumn($table, 'result_extra', 'text');
    $w->addColumn($table, 'pkg_num', 'int');
    $w->addColumn($table, 'int_label_image', 'text');
    $w->addColumn($table, 'label_render_options', 'text');
    $w->addColumn($table, 'udropship_status', 'varchar(20)');
    try {
    $w->addKey($table, 'udropship_status', 'udropship_status', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }
    $w->addColumn($table, 'next_check', 'datetime');
    try {
    $w->addKey($table, 'next_check', 'next_check', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }

} else {
    $eav->addAttribute('quote_address', 'udropship_shipping_details', array('type'=>'static'));
    $eav->addAttribute('quote_item', 'udropship_vendor', array('type' => 'static'));
    $eav->addAttribute('quote_address_item', 'udropship_vendor', array('type'=>'static'));
    $eav->addAttribute('order_item', 'udropship_vendor', array('type' => 'static'));

    $w->addColumn($this->getTable('sales_order'), 'udropship_status', 'tinyint not null');
    try {
    $w->addKey($this->getTable('sales_order'), 'udropship_status', 'udropship_status', 'index');
    } catch (Exception $e) { Mage::log(__FILE__.':'.__LINE__.' '.$e->getMessage()); }

    $eav->addAttribute('order', 'udropship_status', array('type' => 'static'));
    $eav->addAttribute('order', 'udropship_shipping_details', array('type'=>'text'));

    $eav->addAttribute('shipment', 'udropship_vendor', array('type' => 'int'));
    $eav->addAttribute('shipment', 'udropship_status', array('type' => 'int'));
    $eav->addAttribute('shipment', 'base_total_value', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'total_value', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'base_shipping_amount', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'shipping_amount', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'udropship_available_at', array('type' => 'datetime'));
    $eav->addAttribute('shipment', 'udropship_method', array('type' => 'varchar'));
    $eav->addAttribute('shipment', 'udropship_method_description', array('type' => 'text'));
    $eav->addAttribute('shipment', 'base_tax_amount', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'total_cost', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'transaction_fee', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'commission_percent', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'handling_fee', array('type' => 'decimal'));
    $eav->addAttribute('shipment', 'udropship_shipcheck', array('type' => 'varchar'));
    $eav->addAttribute('shipment', 'udropship_vendor_order_id', array('type' => 'varchar'));

    $eav->addAttribute('shipment_item', 'qty_shipped', array('type' => 'decimal'));

    $eav->addAttribute('shipment_track', 'batch_id', array('type' => 'int'));
    $eav->addAttribute('shipment_track', 'label_image', array('type' => 'text'));
    $eav->addAttribute('shipment_track', 'label_format', array('type' => 'varchar'));
    $eav->addAttribute('shipment_track', 'label_pic', array('type' => 'varchar'));
    $eav->addAttribute('shipment_track', 'final_price', array('type' => 'decimal'));
    $eav->addAttribute('shipment_track', 'value', array('type' => 'decimal'));
    $eav->addAttribute('shipment_track', 'length', array('type' => 'decimal'));
    $eav->addAttribute('shipment_track', 'width', array('type' => 'decimal'));
    $eav->addAttribute('shipment_track', 'height', array('type' => 'decimal'));
    $eav->addAttribute('shipment_track', 'result_extra', array('type' => 'text'));
    $eav->addAttribute('shipment_track', 'pkg_num', array('type' => 'int'));
    $eav->addAttribute('shipment_track', 'int_label_image', array('type' => 'text'));
    $eav->addAttribute('shipment_track', 'label_render_options', array('type' => 'text'));
    $eav->addAttribute('shipment_track', 'udropship_status', array('type' => 'varchar'));
    $eav->addAttribute('shipment_track', 'next_check', array('type' => 'datetime'));
}

/*
$trackStatusTable = $this->getTable('sales_order_entity_varchar');
$trackStatusAttrId = $config->getAttribute('shipment_track', 'udropship_status')->getId();
$nextCheckTable = $this->getTable('sales_order_entity_datetime');
$nextCheckAttrId = $config->getAttribute('shipment_track', 'next_check')->getId();
$this->run("
INSERT INTO `{$nextCheckTable}` (entity_type_id, attribute_id, entity_id, `value`)
SELECT v.entity_type_id, '{$nextCheckAttrId}', v.entity_id, '0000-00-00'
FROM `{$trackStatusTable}` v
LEFT JOIN `{$nextCheckTable}` d ON d.entity_id=v.entity_id AND d.attribute_id='{$nextCheckAttrId}'
WHERE v.attribute_id='{$trackStatusAttrId}' AND v.value='S' AND d.value IS NULL;
");
*/

$this->endSetup();