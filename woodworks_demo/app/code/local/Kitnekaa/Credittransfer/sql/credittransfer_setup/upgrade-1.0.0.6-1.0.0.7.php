<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('credittransfer/docs')}` 
ADD `under_verification` INT(1) NULL DEFAULT '1' ;

");
$installer->endSetup();
