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

$this->createDependConfigPath('carriers/udropship/free_shipping_allowed', array(
    'carriers/udropship/free_shipping_enable',
    'carriers/udropship/free_method',
));

$this->createDependConfigPath('carriers/udropship/freeweight_allowed', array(
    'carriers/udropship/free_method',
));

$this->endSetup();
