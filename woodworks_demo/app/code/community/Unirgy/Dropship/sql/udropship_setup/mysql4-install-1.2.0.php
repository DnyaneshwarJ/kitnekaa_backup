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

CREATE TABLE `{$this->getTable('udropship_shipping')}` (
`shipping_id` int(10) unsigned NOT NULL auto_increment,
`shipping_code` varchar(30) NOT NULL,
`shipping_title` varchar(100) NOT NULL,
`days_in_transit` varchar(20) NOT NULL,
PRIMARY KEY  (`shipping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('udropship_shipping_method')}` (
`shipping_id` int(10) unsigned NOT NULL,
`carrier_code` varchar(30) NOT NULL,
`method_code` varchar(30) NOT NULL,
KEY `FK_udropship_shipping_method` (`shipping_id`),
CONSTRAINT `FK_udropship_shipping_method` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('udropship_shipping_website')}` (
`shipping_id` int(10) unsigned NOT NULL,
`website_id` smallint(5) unsigned NOT NULL,
UNIQUE KEY `website_id` (`website_id`,`shipping_id`),
KEY `FK_udropship_shipping_website` (`shipping_id`),
CONSTRAINT `FK_udropship_shipping_website` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_shipping_website_pk` FOREIGN KEY (`website_id`) REFERENCES `{$this->getTable('core_website')}` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('udropship_vendor')}` (
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
`password_hash` varchar(50) default NULL,
`password_enc` varchar(50) default NULL,
`carrier_code` varchar(50) default NULL,
`notify_new_order` tinyint not null default 1,
`label_type` varchar(10) not null default 'PDF',
`test_mode` tinyint(1) not null default 0,
`handling_fee` decimal(12,5) not null,
`ups_shipper_number` varchar(6),
`custom_data_combined` text,
`custom_vars_combined` text,
PRIMARY KEY  (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('udropship_vendor_product')}` (
`vendor_product_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(10) unsigned NOT NULL,
`product_id` int(10) unsigned NOT NULL,
`priority` smallint(5) unsigned NOT NULL,
`carrier_code` varchar(50) default NULL,
PRIMARY KEY  (`vendor_product_id`),
KEY `FK_udropship_vendor_product` (`vendor_id`),
KEY `FK_udropship_vendor_product_entity` (`product_id`),
CONSTRAINT `FK_udropship_vendor_product` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_vendor_product_entity` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('udropship_vendor_shipping')}` (
`vendor_shipping_id` int(11) unsigned NOT NULL auto_increment,
`vendor_id` int(11) unsigned NOT NULL,
`shipping_id` int(10) unsigned NOT NULL,
`account_id` varchar(50) default NULL,
`price_type` tinyint(4) default NULL,
`price` decimal(12,4) default NULL,
`priority` smallint(5) unsigned NOT NULL,
`handling_fee` decimal(12,4) NOT NULL,
PRIMARY KEY  (`vendor_shipping_id`),
KEY `FK_udropship_vendor_shipping_vendor` (`vendor_id`),
KEY `FK_udropship_vendor_shipping` (`shipping_id`),
CONSTRAINT `FK_udropship_vendor_shipping` FOREIGN KEY (`shipping_id`) REFERENCES `{$this->getTable('udropship_shipping')}` (`shipping_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_udropship_vendor_shipping_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `{$this->getTable('udropship_vendor')}` (`vendor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table {$this->getTable('udropship_label_batch')} (
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

create table {$this->getTable('udropship_label_shipment')} (
batch_id int unsigned not null,
order_id int unsigned not null,
shipment_id int unsigned not null,
unique(batch_id, order_id, shipment_id),
key(order_id),
key(shipment_id)
) engine=innodb default charset=utf8;
");

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eav->addAttribute('catalog_product', 'udropship_vendor', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Dropship Vendor',
    'global' => 2,
    'user_defined' => 1,
    'required' => 1,
    'visible' => 1,
    'backend' => 'Unirgy_Dropship_Model_Mysql4_Vendor_Backend',#'udropship_mysql4/vendor_backend',
    'source' => 'Unirgy_Dropship_Model_Vendor_Source',#'udropship/vendor_source',
));

if (version_compare(Mage::getVersion(), '1.3.0', '>=')) {
    $eav->updateAttribute('catalog_product', 'udropship_vendor', 'used_in_product_listing', 1);
}

$this->_conn->addColumn($this->getTable('sales_flat_quote_item'), 'udropship_vendor', 'int unsigned');
$eav->addAttribute('quote_item', 'udropship_vendor', array('type' => 'static'));

$this->_conn->addColumn($this->getTable('sales_flat_order_item'), 'udropship_vendor', 'int unsigned');
$eav->addAttribute('order_item', 'udropship_vendor', array('type' => 'static'));

$eav->addAttribute('shipment', 'udropship_vendor', array('type' => 'int'));
$eav->addAttribute('shipment', 'udropship_status', array('type' => 'int'));

$eav->addAttribute('shipment_track', 'batch_id', array('type' => 'int'));
$eav->addAttribute('shipment_track', 'label_image', array('type' => 'text'));
$eav->addAttribute('shipment_track', 'label_format', array('type' => 'varchar'));
$eav->addAttribute('shipment_track', 'label_pic', array('type' => 'varchar'));
$eav->addAttribute('shipment_track', 'final_price', array('type' => 'decimal'));

$this->endSetup();