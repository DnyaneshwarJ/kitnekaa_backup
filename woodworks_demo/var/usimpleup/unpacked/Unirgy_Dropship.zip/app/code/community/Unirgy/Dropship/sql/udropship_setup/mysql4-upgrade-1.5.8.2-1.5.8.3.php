<?php

$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('udropship_vendor')}
    CHANGE `password_hash` `password_hash` VARCHAR(100),
    CHANGE `password_enc` `password_enc` VARCHAR(100);
");

$this->endSetup();