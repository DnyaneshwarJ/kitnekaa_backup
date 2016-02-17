<?php

$installer = $this;

$installer->startSetup();


$installer->run("CREATE TABLE IF NOT EXISTS `".$this->getTable("profile/profile")."` (
  `id` int unsigned NOT NULL auto_increment,
  `attribute_id` int ,
  `attribute_code` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB charset=utf8 COLLATE=utf8_unicode_ci COMMENT='this table is for storing the attribute code created by profile extension'");


$installer->endSetup();
