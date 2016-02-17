<?php

$this->startSetup();

try {

$this->run("

ALTER TABLE `{$this->getTable('usimplelic_license')}`
    ADD COLUMN `aux_checksum` INT UNSIGNED NULL;

");

} catch (Exception $e) {
    // already exists
}

$this->endSetup();