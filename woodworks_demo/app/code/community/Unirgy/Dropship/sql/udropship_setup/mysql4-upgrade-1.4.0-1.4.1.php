<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$conn = $this->_conn;
$t = $this->getTable('udropship_vendor');
$log = 'var/log/exception.log';

try {
    $conn->addColumn($t, 'url_key', 'varchar(64)');
    $conn->addKey($t, 'IDX_URL_KEY', 'url_key', 'unique');
} catch (Exception $e) {
    if (is_writable($log)) error_log(__FILE__.': '.$e->getMessage()."\n", 3, $log);
}

try {
    $conn->addColumn($t, 'random_hash', 'varchar(64)');
    $conn->addKey($t, 'IDX_HASH', 'random_hash');
} catch (Exception $e) {
    if (is_writable($log)) error_log(__FILE__.': '.$e->getMessage()."\n", 3, $log);
}

try {
    $vendors = Mage::getModel('udropship/vendor')->getCollection();
    foreach ($vendors as $vendor) {
        $vendor->afterLoad()->save();
    }
} catch (Exception $e) {
    if (is_writable($log)) error_log(__FILE__.': '.$e->getMessage()."\n", 3, $log);
}

$this->endSetup();