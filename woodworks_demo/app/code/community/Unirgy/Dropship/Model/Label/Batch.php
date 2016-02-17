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

class Unirgy_Dropship_Model_Label_Batch extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/label_batch');
        parent::_construct();
    }

    public function getVendor()
    {
        if (!$this->hasData('vendor')) {
            if (!$this->getVendorId()) {
                Mage::throwException('No vendor specified');
            }
            $this->setData('vendor', Mage::getModel('udropship/vendor')->load($this->getVendorId()));
        }
        return $this->getData('vendor');
    }

    public function getVendorId()
    {
        if (!$this->hasData('vendor_id')) {
            if (!$this->getVendor()) {
                Mage::throwException('No vendor specified');
            }
            $this->setData('vendor_id', $this->getVendor()->getId());
        }
        return $this->getData('vendor_id');
    }

    protected $_forcedMultipackage=false;
    protected function _isAllowedMultipackage($cCode)
    {
        return in_array($cCode, array('fedex','fedexsoap'))
            || $this->_forcedMultipackage;
    }

    public function processShipments($shipments, $trackData = array(), $flags = array())
    {
        if (empty($trackData)) {
            foreach ($shipments as $shipment) {
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $carrierCode = !empty($method[0]) ? $method[0] : $this->getVendor()->getCarrierCode();
                $maxWeight = max(150, Mage::getStoreConfig('carriers/'.$carrierCode.'/max_package_weight', $shipment->getStoreId()));
                $minWeight = max(0.1, Mage::getStoreConfig('carriers/'.$carrierCode.'/min_package_weight', $shipment->getStoreId()));
                /*
                $numBoxes = max(1, ceil($shipment->getTotalWeight()/$maxWeight));
                for ($idx=0; $idx<$numBoxes; $idx++) {
                    $trackData['length'][] = $this->getVendor()->getDefaultPkgLength();
                    $trackData['height'][] = $this->getVendor()->getDefaultPkgHeight();
                    $trackData['width'][] = $this->getVendor()->getDefaultPkgWidth();
                    if ($idx==$numBoxes-1) {
                        $pkgWeight = $shipment->getTotalWeight()-$maxWeight*($numBoxes-1);
                    } else {
                        $pkgWeight = $maxWeight;
                    }
                    $trackData['value'][] = $shipment->getBaseTotalValue()/$numBoxes;
                    $trackData['weight'][] = $pkgWeight;
                }*/

                $trackData = array();
                $value = $weight = 0;
                $parentItems = array();
                foreach ($shipment->getAllItems() as $item) {

                    $_weight = 0;
                    $orderItem = $item->getOrderItem();
                    if ($orderItem->getParentItem()) {
                        $weightType = $orderItem->getParentItem()->getProductOptionByCode('weight_type');
                        if (null !== $weightType && !$weightType) {
                            $_weight = $item->getWeight();
                        }
                    } else {
                        $weightType = $orderItem->getProductOptionByCode('weight_type');
                        if (null === $weightType || $weightType) {
                            $_weight = $item->getWeight();
                        }
                    }

                    $children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
                    if ($children) {
                        $parentItems[$orderItem->getId()] = $item;
                    }
                    $__qty = $item->getQty();
                    if ($orderItem->isDummy(true)) {
                        if (($_parentItem = $orderItem->getParentItem())) {
                            $__qty = $orderItem->getQtyOrdered()/$_parentItem->getQtyOrdered();
                            if (@$parentItems[$_parentItem->getId()]) {
                                $__qty *= $parentItems[$_parentItem->getId()]->getQty();
                            }
                        } else {
                            $__qty = max(1,$item->getQty());
                        }
                    }

                    for ($i=0;$i<$__qty;$i++) {
                        if ($weight+$_weight>$maxWeight) {
                            $trackData['length'][] = $this->getVendor()->getDefaultPkgLength();
                            $trackData['height'][] = $this->getVendor()->getDefaultPkgHeight();
                            $trackData['width'][] = $this->getVendor()->getDefaultPkgWidth();
                            $trackData['value'][] = $value;
                            $trackData['weight'][] = $weight;
                            $value = 0;
                            $weight = 0;
                        }
                        $weight += $_weight;
                        $value += ($item->getBasePrice() ? $item->getBasePrice() : $item->getPrice());
                    }
                }

                if ($weight>0) {
                    $trackData['length'][] = $this->getVendor()->getDefaultPkgLength();
                    $trackData['height'][] = $this->getVendor()->getDefaultPkgHeight();
                    $trackData['width'][] = $this->getVendor()->getDefaultPkgWidth();
                    $trackData['value'][] = $value;
                    $trackData['weight'][] = $weight;
                }

                $this->_processShipments(array($shipment), $trackData, $flags);
            }
        } else {
            $this->_processShipments($shipments, $trackData, $flags);
        }
        return $this;
    }
    protected function _processShipments($shipments, $trackData = array(), $flags = array())
    {
        if (!$this->getBatchId()) {
            $this->setCreatedAt(now());
            $this->setVendorId($this->getVendor()->getId());
            $this->setLabelType($this->getVendor()->getLabelType());
            $this->save();
        }

        $labelModels = array();

        $success = 0;
        $fromOrderId = null;
        $toOrderId = null;

        if (isset($trackData['length']) && is_array($trackData['length'])) {
            unset($trackData['length']['$ROW']);
            $pkgLength = $pkgHeight = $pkgWidth = $pkgValue = $pkgWeight = array();
            $pcIdx=1; foreach ($trackData['length'] as $wIdx=>$w) {
                $pkgLength[$pcIdx] = @$trackData['length'][$wIdx];
                $pkgHeight[$pcIdx] = @$trackData['height'][$wIdx];
                $pkgWidth[$pcIdx]  = @$trackData['width'][$wIdx];
                $pkgValue[$pcIdx]  = @$trackData['value'][$wIdx];
                $pkgWeight[$pcIdx] = @$trackData['weight'][$wIdx];
                $pcIdx++;
            }
            $totalWeight = array_sum($pkgWeight);
            $trackData['package_count'] = count($pkgWeight);
        }

        $transactionSave = true;#Mage::getModel('core/resource_transaction');
#echo "<pre>";
        foreach ($shipments as $shipment) {
#print_r($shipment->debug); continue;
            if ($shipment->getUdropshipStatus()==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
                $this->addError('Your part of the order is already shipped - %s order(s)');
                continue;
            }
            $storeId = $shipment->getOrder()->getStoreId();
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $storeId);

            $sItemIds = array();
            foreach ($shipment->getAllItems() as $sItem) {
                if ($sItem->getOrderItem()->getIsVirtual()) continue;
                if (!isset($firstOrderItem)) {
                    $firstOrderItem = $sItem->getOrderItem();
                }
                $sItemIds[$sItem->getId()] = array('item' => $sItem);
            }
            if (empty($sItemIds)) {
                //$this->addError('There is no shippable items');
                continue;
            }
            if (isset($firstOrderItem) && $firstOrderItem->getUdpompsManual()) {
                $this->addError('This shipment not configured to generate labels');
                continue;
            }

            try {
                $this->beforeShipmentLabel($this->getVendor(), $shipment);
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $carrierCode = !empty($method[0]) ? $method[0] : $this->getVendor()->getCarrierCode();

                $packageCount = $this->_isAllowedMultipackage($carrierCode) && isset($trackData['package_count']) && is_numeric($trackData['package_count'])
                    ? $trackData['package_count']
                    : 1;

                if (empty($labelModels[$carrierCode])) {
                    $labelModels[$carrierCode] = Mage::helper('udropship')->getLabelCarrierInstance($carrierCode)
                        ->setBatch($this)
                        ->setVendor($this->getVendor());
                }
                $labelModels[$carrierCode]->setUdropshipPackageCount($packageCount);

                $mpsRequests = array();
                if (Mage::helper('udropship')->isUdpoMpsAvailable($carrierCode)) {
                    unset($trackData['weight']);
                    unset($trackData['value']);
                    unset($trackData['package_count']);
                    foreach ($shipment->getAllItems() as $sItem) {
                        if ($sItem->getOrderItem()->getIsVirtual()) continue;
                        if ($sItem->getOrderItem()->getUdpompsShiptype() == Unirgy_DropshipPoMps_Model_Source::SHIPTYPE_ROW_SEPARATE) {
                            $mpsRequests[] = array(
                                'items' => array($sItem->getId() => array('item' => $sItem))
                            );
                            unset($sItemIds[$sItem->getId()]);
                        } elseif ($sItem->getOrderItem()->getUdpompsShiptype() == Unirgy_DropshipPoMps_Model_Source::SHIPTYPE_ITEM_SEPARATE) {
                            for ($i=1; $i<=$sItem->getQty(); $i++) {
                                $splitWeight = $sItem->getOrderItem()->getUdpompsSplitWeight();
                                if (!is_array($splitWeight)) {
                                    $splitWeight = Mage::helper('core')->jsonDecode($splitWeight);
                                }
                                if (!empty($splitWeight) && is_array($splitWeight)) {
                                    foreach ($splitWeight as $_sWeight) {
                                        $mpsRequests[] = array(
                                            'items' => array($sItem->getId() => array(
                                                'item' => $sItem,
                                                'qty' => 1,
                                                'weight' => !empty($_sWeight['weight']) ? $_sWeight['weight'] : $sItem->getWeight()
                                            )),
                                        );
                                    }
                                } else {
                                    $mpsRequests[] = array(
                                        'items' => array($sItem->getId() => array('item' => $sItem)),
                                    );
                                }
                            }
                            unset($sItemIds[$sItem->getId()]);
                        }
                    }
                    if (!empty($sItemIds)) {
                        $mpsRequests[] = array(
                            'items' => $sItemIds
                        );
                    }
                }
                if (empty($mpsRequests)) {
                    $sItemIds = array();
                    foreach ($shipment->getAllItems() as $sItem) {
                        if ($sItem->getOrderItem()->getIsVirtual()) continue;
                        $sItemIds[$sItem->getId()] = array('item' => $sItem);
                    }
                    for ($pcIdx=1; $pcIdx<=$packageCount; $pcIdx++) {
                        $mpsRequests[] = array(
                            'items' => $sItemIds
                        );
                    }
                }

                $labelModels[$carrierCode]->setUdropshipPackageCount(count($mpsRequests));

                $newTracks = array();

                for ($pcIdx=1; $pcIdx<=count($mpsRequests); $pcIdx++) {

                    $labelModels[$carrierCode]->setMpsRequest($mpsRequests[$pcIdx-1]);

                    $track = Mage::getModel('sales/order_shipment_track')
                        ->setBatchId($this->getBatchId());
                    if (!empty($trackData)) {
                        if (isset($pkgWeight)) {
                            $trackData['total_weight'] = $totalWeight;
                            $trackData['length'] = $pkgLength[$pcIdx];
                            $trackData['height'] = $pkgHeight[$pcIdx];
                            $trackData['width']  = $pkgWidth[$pcIdx];
                            $trackData['value']  = $pkgValue[$pcIdx];
                            $trackData['weight'] = $pkgWeight[$pcIdx];
                        }
                        $track->addData($trackData);
                    }
                    $shipment->addTrack($track);

                    $labelModels[$carrierCode]->setUdropshipPackageIdx($pcIdx);
                    $labelModels[$carrierCode]->requestLabel($track);

                    $newTracks[] = $track;

                    $success++;
                    
                }
                Mage::helper('udropship')->processTrackStatus($newTracks, $transactionSave, !empty($flags['mark_shipped']));
                $labelModels[$carrierCode]->unsUdropshipPackageIdx();
                $labelModels[$carrierCode]->unsUdropshipPackageCount();
                $labelModels[$carrierCode]->unsUdropshipMasterTrackingId();
                $this->afterShipmentLabel($this->getVendor(), $shipment);
            } catch (Exception $e) {
                $this->afterShipmentLabel($this->getVendor(), $shipment);
            	Mage::dispatchEvent('udropship_shipment_label_request_failed', array('shipment'=>$shipment, 'error'=>$e->getMessage()));
                $this->addError($e->getMessage().' - %s order(s)');
                continue;
            }

            $orderId = $shipment->getOrder() ? $shipment->getOrder()->getIncrementId() : $shipment->getOrderIncrementId();
            if (is_null($fromOrderId)) {
                $fromOrderId = $orderId;
                $toOrderId = $orderId;
            } else {
                $fromOrderId = min($fromOrderId, $orderId);
                $toOrderId = max($toOrderId, $orderId);
            }

            if ($shipment->getUdpo() && $shipment->getUdpo()->getUseLabelShippingAmount()) {
                $_orderRate = $shipment->getOrder()->getBaseToOrderRate() > 0 ? $shipment->getOrder()->getBaseToOrderRate() : 1;
                $_baseSa = 0;
                foreach ($shipment->getAllTracks() as $t) {
                    $_baseSa += $t->getFinalPrice();
                }
                $_sa = Mage::app()->getStore()->roundPrice($_orderRate*$_baseSa);
                $shipment->setShippingAmount($_sa)->setBaseShippingAmount($_baseSa);
                $shipment->getResource()->saveAttribute($shipment, 'base_shipping_amount');
                $shipment->getResource()->saveAttribute($shipment, 'shipping_amount');
            }

        }
#exit;
        $this->setTitle('Orders IDs: '.$fromOrderId.' - '.$toOrderId);
        $this->setShipmentCnt($this->getShipmentCnt()+$success);

        if (!empty($track)) {
            $this->setLastTrack($track);
        }

        if (!$this->getShipmentCnt()) {
            $this->delete();
        } else {
            $this->save();
        }

        return $this;
    }

    public function beforeShipmentLabel($vendor, $shipment)
    {
        Mage::helper('udropship/label')->beforeShipmentLabel($vendor, $shipment);
        return $this;
    }

    public function afterShipmentLabel($vendor, $shipment)
    {
        Mage::helper('udropship/label')->afterShipmentLabel($vendor, $shipment);
        return $this;
    }

    public function renderShipments($shipments)
    {
        $tracks = array();
        foreach ($shipments as $shipment) {
            foreach ($shipment->getAllTracks() as $track) {
                $tracks[] = $track;
            }
        }
        $this->setTracks($tracks);
        $this->setVendorId($this->getVendor()->getId());
        $this->setLabelType($this->getVendor()->getLabelType());
        return $this;
    }

    public function refundOrderLabels($orderIds)
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('entity_id', $orderIds);

        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($this->getCarrierCode());

        $success = 0;
        $entityIds = array();
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $labelIds = array();
            $shipmentNum = 0;
            $shipments = $order->getShipmentsCollection();
            if ($shipments->count()) {
                foreach ($shipments as $shipment) {
                    $tracks = $shipment->getTracksCollection();
                    foreach ($tracks as $track) {
                        try {
                            $labelModel->refundLabel($track);
                        } catch (Exception $e) {
                            $this->addError($e->getMessage());
                            continue;
                        }
                        $labelIds[] = $track->getNumber();
                        $track->delete();
                    }
                    $shipmentNum++;
                    $shipment->delete();
                }
                foreach ($order->getAllItems() as $item) {
                    $item->setQtyShipped(0)->save();
                }
            }
            if ($labelIds) {
                $order->addStatusToHistory('processing', 'Removed shipments, refunded labels: '.join(', ', $labelIds));
                $success++;
            } elseif ($shipmentNum) {
                $order->addStatusToHistory('processing', 'Removed shipments, no eligible labels were found');
                $this->addError('No labels found in order - %s orders');
            } else {
                $this->addError('No shipments found in order - %s orders');
            }
            if ($order->getStatus()!='processing') {
                $order->setStatus('processing')->setState('processing')->save();
            }
        }
        $this->setSuccess($success);
        return $this;
    }

    public function addError($e)
    {
        $errors = $this->getErrors();
        if (!$errors) {
            $errors = array();
        }
        if (empty($errors[$e])) {
            $errors[$e] = 0;
        }
        $errors[$e]++;
        $this->setErrors($errors);
        return $this;
    }

    public function getErrorMessages($sep="\n")
    {
        if (!$this->getErrors()) {
            return '';
        }
        $errors = '';
        foreach ($this->getErrors() as $msg=>$cnt) {
            $errors .= sprintf($msg, $cnt).$sep;
        }
        return $errors;
    }

    public function getFileName()
    {
        $labelModel = Mage::helper('udropship')->getLabelTypeInstance($this->getLabelType());
        return $labelModel->getFullFileName('batch-'.$this->getBatchId());
    }

    public function getBatchTracks()
    {
        if ($this->hasTracks()) {
            return $this->getTracks();
        }
        $tracks = Mage::getModel('sales/order_shipment_track')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('batch_id', $this->getBatchId())
            ->addAttributeToSort('order_id')
            ->addAttributeToSort('entity_id');

        return $tracks;
    }

    public function prepareLabelsDownloadResponse()
    {
        $labelModel = Mage::helper('udropship')->getLabelTypeInstance($this->getLabelType());
        $labelModel->setBatch($this)->printBatch();
    }
}
