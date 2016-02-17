<?php

/**
 * Created on April 25th 2015
 *
 * @author Bobcares
 * @desc Creating necessary tables for quote2sales module
 */
echo 'Running Bobcares Quote2Sales upgrade from 0.8.2 to 0.8.3: ' . get_class($this) . "\n <br /> \n";
$installer = $this;
$installer->startSetup();

/* Changeing table name to add table prefix */
$installer->run("ALTER TABLE  `quote2sales_requests`  RENAME {$this->getTable('quote2sales_requests')};"
);
$installer->run("ALTER TABLE  `quote2sales_requests_status`  RENAME {$this->getTable('quote2sales_requests_status')};"
);

echo "Done Running setup \n";
$installer->endSetup();

