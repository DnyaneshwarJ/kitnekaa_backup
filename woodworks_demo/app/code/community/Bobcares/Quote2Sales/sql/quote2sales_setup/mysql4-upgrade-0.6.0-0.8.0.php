<?php
/**
* Created on 21 Jan 2015
*
* @author Bobcares
* @desc Creating necessary tables for quote2sales module
*/
echo 'Running Bobcares Quote2Sales upgrade from 0.7.2 to 0.8.0: '.get_class($this)."\n <br /> \n";
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `quote2sales_requests` ADD COLUMN status varchar(50);
ALTER TABLE `quote2sales_requests` ADD COLUMN quote_id int unsigned DEFAULT NULL;
ALTER TABLE `quote2sales_requests`  ADD COLUMN order_id int unsigned DEFAULT NULL;");


echo "Done Running setup \n";

$installer->endSetup();

