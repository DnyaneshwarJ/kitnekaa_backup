<?php

$this->startSetup();

try {

$this->run("

ALTER TABLE `{$this->getTable('usimplelic_license')}`
    CONVERT TO CHARACTER SET  'utf8';

");

} catch (Exception $e) {
    // already exists
}

$this->endSetup();