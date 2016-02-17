<?php

$this->startSetup();

$conn = $this->_conn;

$this->run("
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

$this->endSetup();