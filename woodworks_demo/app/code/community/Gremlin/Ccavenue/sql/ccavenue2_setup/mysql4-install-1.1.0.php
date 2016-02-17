<?php
/**
 * Install MySQL Tables
 *
 * @category    Installer
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

$installer = $this;
$installer->startSetup();

$sql = <<<SQLTEXT
CREATE TABLE IF NOT EXISTS `{$this->getTable( 'gremlin_ccavenue_redirect' )}` (
  `redirect_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `merchant_id` int(10) unsigned NOT NULL,
  `amount` float(10,0) unsigned NOT NULL,
  `redirect_url` varchar(255) NOT NULL,
  `cancel_url` varchar(255) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `language` char(2) NOT NULL,
  `encrypted_data` text NOT NULL,
  `access_code` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `billing_name` varchar(255) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_state` varchar(255) NOT NULL,
  `billing_zip` varchar(255) NOT NULL,
  `billing_country` varchar(255) NOT NULL,
  `billing_tel` varchar(255) NOT NULL,
  `billing_email` varchar(255) NOT NULL,
  `delivery_name` varchar(255) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_city` varchar(255) DEFAULT NULL,
  `delivery_state` varchar(255) DEFAULT NULL,
  `delivery_zip` varchar(255) DEFAULT NULL,
  `delivery_country` varchar(255) DEFAULT NULL,
  `delivery_tel` varchar(255) DEFAULT NULL,
  `merchant_param1` varchar(255) DEFAULT NULL,
  `merchant_param2` varchar(255) DEFAULT NULL,
  `merchant_param3` varchar(255) DEFAULT NULL,
  `merchant_param4` varchar(255) DEFAULT NULL,
  `merchant_param5` varchar(255) DEFAULT NULL,
  `ip_address` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`redirect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `{$this->getTable( 'gremlin_ccavenue_response' )}` (
  `response_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `tracking_id` int(10) unsigned NOT NULL,
  `bank_ref_no` varchar(100) NOT NULL,
  `order_status` varchar(100) NOT NULL,
  `failure_message` varchar(255) NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `card_name` varchar(100) NOT NULL,
  `status_code` varchar(100) NOT NULL,
  `status_message` varchar(255) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `amount` float(10,0) unsigned NOT NULL,
  `billing_name` varchar(255) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_city` varchar(255) NOT NULL,
  `billing_state` varchar(255) NOT NULL,
  `billing_zip` varchar(255) NOT NULL,
  `billing_country` varchar(255) NOT NULL,
  `billing_tel` varchar(255) NOT NULL,
  `billing_email` varchar(255) NOT NULL,
  `delivery_name` varchar(255) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_city` varchar(255) DEFAULT NULL,
  `delivery_state` varchar(255) DEFAULT NULL,
  `delivery_zip` varchar(255) DEFAULT NULL,
  `delivery_country` varchar(255) DEFAULT NULL,
  `delivery_tel` varchar(255) DEFAULT NULL,
  `merchant_param1` varchar(255) DEFAULT NULL,
  `merchant_param2` varchar(255) DEFAULT NULL,
  `merchant_param3` varchar(255) DEFAULT NULL,
  `merchant_param4` varchar(255) DEFAULT NULL,
  `merchant_param5` varchar(255) DEFAULT NULL,
  `ip_address` varchar(15) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`response_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SQLTEXT;

$installer->run( $sql );
$installer->endSetup();
