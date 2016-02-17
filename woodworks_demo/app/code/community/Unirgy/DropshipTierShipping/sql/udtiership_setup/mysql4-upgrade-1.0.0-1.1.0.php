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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');
$hlpr = Mage::helper('udtiership');

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$tsConf = $conn->fetchAll(
    $conn->select()->from($installer->getTable('core/config_data'), array('config_id','value'))
        ->where('path=?', 'carriers/udtiership/rates')
);

if (!empty($tsConf)) {
    foreach ($tsConf as $tsc) {
        $tscVal = unserialize($tsc['value']);
        if (!empty($tscVal) && is_array($tscVal)) {
            $newTscVal = array();
            foreach ($tscVal as $_tk=>$_tv) {
                if (false === strpos($_tk, ':')) {
                    $_tk = preg_replace('/([^-])-/', '$1:', $_tk);
                }
                $newTscVal[$_tk] = $_tv;
            }
            $tsc['value'] = serialize($newTscVal);
            $conn->update($installer->getTable('core/config_data'), $tsc, array('config_id=?'=>$tsc['config_id']));
        }
    }
}

$vtsConf = $conn->fetchAll(
    $conn->select()->from($installer->getTable('udropship/vendor'), array('vendor_id','tiership_rates'))
);

if (!empty($vtsConf)) {
    foreach ($vtsConf as $vtsc) {
        $vtscVal = unserialize($vtsc['tiership_rates']);
        if (!empty($vtscVal) && is_array($vtscVal)) {
            $newVtscVal = array();
            foreach ($vtscVal as $_tk=>$_tv) {
                if (false === strpos($_tk, ':')) {
                    $_tk = preg_replace('/-([^-]+)-/', '-', $_tk);
                    $_tk = preg_replace('/([^-])-/', '$1:', $_tk);
                }
                $newVtscVal[$_tk] = $_tv;
            }
            $vtsc['tiership_rates'] = serialize($newVtscVal);
            $conn->update($installer->getTable('udropship/vendor'), $vtsc, array('vendor_id=?'=>$vtsc['vendor_id']));
        }
    }
}

$installer->endSetup();