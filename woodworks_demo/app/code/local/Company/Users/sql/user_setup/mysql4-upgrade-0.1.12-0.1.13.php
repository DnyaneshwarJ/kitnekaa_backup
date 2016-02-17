<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('users/company')}` 
 ADD `is_member` INT(1) NOT NULL DEFAULT '0';
");
$installer->endSetup();