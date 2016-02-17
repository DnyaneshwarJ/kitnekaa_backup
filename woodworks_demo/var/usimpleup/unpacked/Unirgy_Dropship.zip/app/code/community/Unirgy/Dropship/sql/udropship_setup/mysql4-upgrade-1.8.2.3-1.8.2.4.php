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
 
$installer = $this;
$w = $this->_conn;
$installer->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('udropship_vendor_statement_adjustment')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_id` varchar(64) DEFAULT NULL,
  `statement_id` varchar(30) DEFAULT NULL,
  `shipment_id` varchar(50) NOT NULL DEFAULT '',
  `amount` decimal(12,4) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `paid` tinyint,
  `comment` text,
  UNIQUE `UNQ_ADJUSTMENT_ID` (`adjustment_id`),
  KEY `IDX_STATEMENT_ID` (`statement_id`),
  KEY `IDX_SHIPMENT_ID` (`shipment_id`),
  KEY `IDX_CREATED_AT` (`created_at`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();