<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('quote2sales_item_files')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('quote2sales_item_files')}` (
  `file_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(200) DEFAULT NULL,
  `quote_list_id` int,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;
");
$installer->endSetup();