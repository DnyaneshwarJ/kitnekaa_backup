<?php

$this->startSetup();

try {

$this->run("

ALTER TABLE `{$this->getTable('usimplelic_license')}`
    ADD COLUMN `server_restriction1` TEXT NULL AFTER server_restriction,
    ADD COLUMN `server_restriction2` TEXT NULL AFTER server_restriction1;

");

} catch (Exception $e) {
    // already exists
}

$this->endSetup();