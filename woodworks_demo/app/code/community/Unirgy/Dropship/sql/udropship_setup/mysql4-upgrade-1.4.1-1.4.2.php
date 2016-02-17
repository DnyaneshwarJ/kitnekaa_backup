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

$hlp = Mage::helper('udropship');

$conn = $this->_conn;
$eavConfig = Mage::getSingleton('eav/config');
$eavSetup = new Mage_Eav_Model_Entity_Setup('sales_setup');

$eavSetup->addAttribute('shipment', 'udropship_method', array('type' => 'varchar'));
$eavSetup->addAttribute('shipment', 'udropship_method_description', array('type' => 'text'));

$oldMethodAttr = $eavConfig->getAttribute('order', 'shipping_method');
$detailsAttr = $eavConfig->getAttribute('order', 'udropship_shipping_details');
$newMethodAttr = $eavConfig->getAttribute('shipment', 'udropship_method');
$newMethodDescrAttr = $eavConfig->getAttribute('shipment', 'udropship_method_description');
$descrAttr = $eavConfig->getAttribute('shipment', 'udropship_method_description');
$vendorAttr = $eavConfig->getAttribute('shipment', 'udropship_vendor');
$shipOrderAttr = $eavConfig->getAttribute('shipment', 'order_id');

$s = $conn->select()
    // shipments
    ->from(array('s'=>$this->getTable('sales_order_entity')), array('shipment_id'=>'entity_id'))
    // order id
    ->join(array('so'=>$this->getTable('sales_order_entity_int')), "so.entity_id=s.entity_id AND so.attribute_id={$shipOrderAttr->getId()}", array())
    // vendor id
    ->join(array('sv'=>$this->getTable('sales_order_entity_int')), "sv.entity_id=s.entity_id AND sv.attribute_id={$vendorAttr->getId()}", array())
    // orders
    ->join(array('o'=>$this->getTable('sales_order')), "o.entity_id=so.value", array())
    // order shipping method (old)
    ->join(array('osm'=>$this->getTable('sales_order_varchar')), "osm.entity_id=o.entity_id AND osm.attribute_id={$oldMethodAttr->getId()} AND osm.value like 'udropship_%'", array('method'=>'value'))
    // vendors
    ->join(array('v'=>$this->getTable('udropship_vendor')), 'v.vendor_id=sv.value', array('vendor_id'))
    // vendors shipping
    ->join(array('vs'=>$this->getTable('udropship_shipping')), "CONCAT('udropship_',vs.shipping_code)=osm.value", array())
    // vendors shipping method
    ->join(array('vsm'=>$this->getTable('udropship_shipping_method')), 'vsm.shipping_id=vs.shipping_id AND vsm.carrier_code=v.carrier_code', array('carrier_code', 'method_code'))
    // order vendor shipping details
    ->joinLeft(array('osd'=>$this->getTable('sales_order_text')), 'osd.entity_id=o.entity_id', array('details_id'=>'value_id', 'details'=>'value'))
    // shipping vendor method (new)
    ->joinLeft(array('sm'=>$this->getTable('sales_order_entity_varchar')), "sm.entity_id=s.entity_id AND sm.attribute_id={$newMethodAttr->getId()}", array('new_method'=>'value'))
;

/*
$s = $conn->select()
    ->from(array('ot'=>$this->getTable('sales_order_text')), array('details_id'=>'value_id', 'details'=>'value'))
        ->where('ot.attribute_id=?', $detailsAttr->getId())
        ->where("ot.value<>''")
    ->join(array('om'=>$this->getTable('sales_order_varchar')), 'om.entity_id=ot.entity_id and om.attribute_id='.$oldMethodAttr->getId(), array('method'=>'value'))
        ->where("om.value like 'udropship_%'")
    ->joinLeft(array('s'=>$this->getTable('sales_order_entity')), 's.parent_id=ot.entity_id and s.entity_type_id='.$newMethodAttr->getEntityTypeId(), array('shipment_id'=>'entity_id'))
    ->joinLeft(array('sv'=>$this->getTable('sales_order_entity_int')), 'sv.entity_id=s.entity_id and sv.attribute_id='.$vendorAttr->getId(), array('vendor_id'=>'value'))
    ->joinLeft(array('sm'=>$this->getTable('sales_order_entity_varchar')), 'sv.entity_id=s.entity_id and sm.attribute_id='.$newMethodAttr->getId(), array('new_method'=>'value'))
;
echo $s; exit;
*/
$q = $conn->query($s);

$details = array();
while ($o = $q->fetch()) {
    if (!empty($o['shipment_id']) && empty($o['new_method'])) { // shipments exist but without method
        $method = $o['carrier_code'].'_'.$o['method_code'];
        $conn->insert($this->getTable('sales_order_entity_varchar'), array(
            'entity_type_id' => $newMethodAttr->getEntityTypeId(),
            'attribute_id' => $newMethodAttr->getId(),
            'entity_id' => $o['shipment_id'],
            'value' => $method,
        ));
        /*
        $conn->insert($this->getTable('sales_order_entity_text'), array(
            'entity_type_id' => $newMethodDescrAttr->getEntityTypeId(),
            'attribute_id' => $newMethodDescrAttr->getId(),
            'entity_id' => $o['shipment_id'],
            'value' => $hlp->getVendor($o['vendor_id'])->getShippingMethodName($method, true),
        ));
        */
    }

    if (!empty($o['details']) && empty($details[$o['details_id']])) { // convert details to new version
        $m = explode('_', $o['method']);
        $d = Zend_Json::decode($o['details']);
        if (empty($d) || empty($d['methods'][$m[1]])) {
            $d = true;
        } else {
            foreach ($d['methods'][$m[1]]['vendors'] as $vId=>$r) {
                $d['methods'][$vId] = array(
                    'code' => $r['carrier'].'_'.$r['method'],
                    'cost' => $r['cost'],
                    'price' => $r['price'],
                    'carrier_title' => $r['carrier'],
                    'method_title' => $r['method'],
                );
            }
            foreach ($d['methods'] as $cCode=>$r) {
                if (!is_numeric($cCode)) {
                    unset($d['methods'][$cCode]);
                }
            }
            $conn->update($this->getTable('sales_order_text'), array('value' => Zend_Json::encode($d)), 'value_id='.$o['details_id']);
        }
        $details[$o['details_id']] = $d;
    }
}

$this->endSetup();