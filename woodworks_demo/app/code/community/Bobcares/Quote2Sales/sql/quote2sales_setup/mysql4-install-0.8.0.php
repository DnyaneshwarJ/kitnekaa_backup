<?php
/**
* Created on 21 Jan 2015
*
* @author Bobcares
* @desc Creating necessary tables for quote2sales module
*/
echo 'Running Bobcares Quote2Sales Install 0.8.0: '.get_class($this)."\n <br /> \n";
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `quote2sales_requests`;

CREATE TABLE IF NOT EXISTS `quote2sales_requests` (
  `request_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `comment` text,
  `status` varchar(50) DEFAULT NULL,
  `quote_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

");


echo "Done Running setup \n";

$installer->endSetup();

