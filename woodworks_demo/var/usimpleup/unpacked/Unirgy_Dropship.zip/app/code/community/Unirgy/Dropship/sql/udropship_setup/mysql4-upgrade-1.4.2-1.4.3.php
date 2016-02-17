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
$conn->addColumn($t, 'created_at', 'timestamp');
$conn->addKey($t, 'IDX_created', 'created_at');
$this->run("update {$t} set created_at=now() where created_at is null");

$this->endSetup();