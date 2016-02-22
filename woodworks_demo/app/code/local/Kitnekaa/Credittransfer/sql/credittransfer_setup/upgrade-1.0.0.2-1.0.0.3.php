<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$installer->getTable('credittransfer/docneeded')}` 
ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY;

");
$installer->endSetup();