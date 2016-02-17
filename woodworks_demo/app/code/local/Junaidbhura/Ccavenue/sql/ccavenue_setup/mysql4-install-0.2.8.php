<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `{$this->getTable( 'junaidbhura_ccavenue_redirect' )}` (
  `ccavenue_redirect_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` varchar(25) NOT NULL,
  `amount` float unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `redirect_url` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `billing_cust_name` varchar(255) NOT NULL,
  `billing_cust_address` text NOT NULL,
  `billing_cust_country` varchar(255) NOT NULL,
  `billing_cust_state` varchar(255) NOT NULL,
  `billing_zip` varchar(100) NOT NULL,
  `billing_cust_tel` varchar(100) NOT NULL,
  `billing_cust_email` varchar(255) NOT NULL,
  `delivery_cust_name` varchar(255) NOT NULL,
  `delivery_cust_address` text NOT NULL,
  `delivery_cust_country` varchar(255) NOT NULL,
  `delivery_cust_state` varchar(255) NOT NULL,
  `delivery_cust_tel` varchar(100) NOT NULL,
  `billing_cust_notes` text,
  `merchant_param` varchar(255) DEFAULT NULL,
  `billing_cust_city` varchar(255) NOT NULL,
  `billing_zip_code` varchar(100) NOT NULL,
  `delivery_cust_city` varchar(255) NOT NULL,
  `delivery_zip_code` varchar(100) NOT NULL,
  `ccavenue_redirect_ip` varchar(50) NOT NULL,
  `ccavenue_redirect_dtime` datetime NOT NULL,
  PRIMARY KEY (`ccavenue_redirect_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable( 'junaidbhura_ccavenue_response' )}` (
  `ccavenue_response_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` varchar(25) NOT NULL,
  `amount` float unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `merchant_param` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `authdesc` varchar(10) NOT NULL,
  `card_category` varchar(255) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `ccavenue_response_ip` varchar(50) NOT NULL,
  `ccavenue_response_dtime` datetime NOT NULL,
  PRIMARY KEY (`ccavenue_response_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SQLTEXT;

$installer->run($sql);

$installer->endSetup();