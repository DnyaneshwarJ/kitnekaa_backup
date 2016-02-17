<?php

$installer = $this;

$installer->startSetup();


$installer->run("ALTER TABLE  `".$this->getTable("profile/profile")."` ADD sort_order int AFTER attribute_code;");


$installer->endSetup();
