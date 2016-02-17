<?php

$this->startSetup();

$this->run("
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
");

$this->run("
create table {$this->getTable('udropship_label_shipment')} (
batch_id int unsigned not null,
order_id int unsigned not null,
shipment_id int unsigned not null,
unique(batch_id, order_id, shipment_id),
key(order_id),
key(shipment_id)
) engine=innodb default charset=utf8;
");


$this->_conn->addColumn($this->getTable('udropship_vendor'), 'test_mode', "tinyint(1) not null default 0");

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'notify_new_order', "tinyint not null default 1");
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'label_type', "varchar(10) not null default 'PDF'");
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'handling_fee', 'decimal(12,5) not null');

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'vendor_attn', "varchar(255) not null after vendor_name");
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'telephone', "varchar(50) not null after region");

$this->_conn->addColumn($this->getTable('udropship_vendor'), 'ups_shipper_number', "varchar(6)");
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'custom_data_combined', "text");
$this->_conn->addColumn($this->getTable('udropship_vendor'), 'custom_vars_combined', "text");

$eav = new Mage_Eav_Model_Entity_Setup('sales_setup');
$eav->addAttribute('shipment_track', 'batch_id', array('type' => 'int'));
$eav->addAttribute('shipment_track', 'label_image', array('type' => 'text'));
$eav->addAttribute('shipment_track', 'label_format', array('type' => 'varchar'));
$eav->addAttribute('shipment_track', 'label_pic', array('type' => 'varchar'));
$eav->addAttribute('shipment_track', 'final_price', array('type' => 'decimal'));

$this->endSetup();