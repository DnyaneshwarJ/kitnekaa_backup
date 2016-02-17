<?php

/**
 * Created on May 16th 2015
 *
 * @author Bobcares
 * @desc Creating a column for seller comment in quote2sales_request table
 */
//echo 'Running Bobcares Quote2Sales upgrade from 0.8.2 to 0.8.5: ' . get_class($this) . "\n <br /> \n";
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('quote2sales_requests')}` ADD COLUMN  frequency VARCHAR(255);");
//echo "Done Running setup \n";

$installer->endSetup();
