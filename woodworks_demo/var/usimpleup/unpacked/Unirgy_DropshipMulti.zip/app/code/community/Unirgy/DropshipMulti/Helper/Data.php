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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMulti_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_customerGroups;
    public function getCustomerGroups()
    {
        if ($this->_customerGroups===null) {
            $this->_customerGroups = array();
            $collection = Mage::getModel('customer/group')->getCollection();
            foreach ($collection as $item) {
                $this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }
        return $this->_customerGroups;
    }

    protected $_multiVendorData = array();

    public function isActive($store=null)
    {
        $method = Mage::getStoreConfig('udropship/stock/availability', $store);
        $config = Mage::getConfig()->getNode("global/udropship/availability_methods/$method");
        return $config && $config->is('multi');
    }
    public function isActiveReassign($store=null)
    {
        $method = Mage::getStoreConfig('udropship/stock/reassign_availability', $store);
        $config = Mage::getConfig()->getNode("global/udropship/availability_methods/$method");
        return $config && $config->is('multi');
    }

    public function clearMultiVendorData()
    {
        $this->_multiVendorData = array();
        return $this;
    }

    public function getVendorSku($pId, $vId, $defaultSku=null)
    {
        $collection = Mage::getModel('udropship/vendor_product')->getCollection()
            ->addProductFilter($pId)
            ->addVendorFilter($vId);
        foreach ($collection as $item) {
            return $item->getVendorSku() ? $item->getVendorSku() : $defaultSku;
        }
        return $defaultSku;
    }

    public function getActiveMultiVendorData($items, $joinVendors=false, $force=false)
    {
        return $this->_getMultiVendorData($items, $joinVendors, $force, true);
    }

    public function getMultiVendorData($items, $joinVendors=false, $force=false)
    {
        return $this->_getMultiVendorData($items, $joinVendors, $force, false);
    }
    protected function _getMultiVendorData($items, $joinVendors=false, $force=false, $isActive=false)
    {
        $key = $joinVendors ? 'vendors,' : 'novendors,';
        $key .= $isActive ? 'active,' : 'inactive,';
        $productIds = array();
        foreach ($items as $item) {
            if ($item instanceof Varien_Object) {
                $pId = $item->hasProductId() ? $item->getProductId() : $item->getEntityId();
                $key .= $pId.':'.$item->getQty().',';
                $productIds[] = $pId;
            } elseif (is_scalar($item)) {
                $key .= $item;
                $productIds[] = $item;
            }
        }
        if (empty($this->_multiVendorData[$key]) || $force) {
            $collection = Mage::getModel('udropship/vendor_product')->getCollection()
                ->addProductFilter($productIds);
            if ($isActive) {
                $collection->getSelect()->where('main_table.status>0');
            }
            if ($joinVendors || $isActive) {
                $res = Mage::getSingleton('core/resource');
                $collection->getSelect()
                    ->join(
                        array('v'=>$res->getTableName('udropship_vendor')),
                        'v.vendor_id=main_table.vendor_id',
                        $joinVendors ? '*' : array()
                    );
                if ($isActive) {
                    $collection->getSelect()->where("v.status='A'");
                }
            }
            $this->_multiVendorData[$key] = $collection;
        }
        return $this->_multiVendorData[$key];
    }

    public function getActiveMvGroupPrice($items, $joinVendors=false, $force=false)
    {
        return $this->_getMvGroupPrice($items, $joinVendors, $force, true);
    }
    public function getMvGroupPrice($items, $joinVendors=false, $force=false)
    {
        return $this->_getMvGroupPrice($items, $joinVendors, $force, false);
    }
    protected function _getMvGroupPrice($items, $joinVendors=false, $force=false, $isActive=false)
    {
        $key = $joinVendors ? 'vendors,' : 'novendors,';
        $key .= $isActive ? 'active,' : 'inactive,';
        $productIds = array();
        foreach ($items as $item) {
            if ($item instanceof Varien_Object) {
                $pId = $item->hasProductId() ? $item->getProductId() : $item->getEntityId();
                $key .= $pId.':'.$item->getQty().',';
                $productIds[] = $pId;
            } elseif (is_scalar($item)) {
                $key .= $item;
                $productIds[] = $item;
            }
        }
        if (empty($this->_mvGroupPrice[$key]) || $force) {
            $collection = Mage::getModel('udmulti/groupPrice')->getCollection()
                ->joinMultiVendorData($isActive, $joinVendors)
                ->addProductFilter($productIds);
            $this->_mvGroupPrice[$key] = $collection;
        }
        return $this->_mvGroupPrice[$key];
    }

    public function getActiveMvTierPrice($items, $joinVendors=false, $force=false)
    {
        return $this->_getMvTierPrice($items, $joinVendors, $force, true);
    }
    public function getMvTierPrice($items, $joinVendors=false, $force=false)
    {
        return $this->_getMvTierPrice($items, $joinVendors, $force, false);
    }
    protected function _getMvTierPrice($items, $joinVendors=false, $force=false, $isActive=false)
    {
        $key = $joinVendors ? 'vendors,' : 'novendors,';
        $key .= $isActive ? 'active,' : 'inactive,';
        $productIds = array();
        foreach ($items as $item) {
            if ($item instanceof Varien_Object) {
                $pId = $item->hasProductId() ? $item->getProductId() : $item->getEntityId();
                $key .= $pId.':'.$item->getQty().',';
                $productIds[] = $pId;
            } elseif (is_scalar($item)) {
                $key .= $item;
                $productIds[] = $item;
            }
        }
        if (empty($this->_mvTierPrice[$key]) || $force) {
            $collection = Mage::getModel('udmulti/tierPrice')->getCollection()
                ->joinMultiVendorData($isActive, $joinVendors)
                ->addProductFilter($productIds);
            $this->_mvTierPrice[$key] = $collection;
        }
        return $this->_mvTierPrice[$key];
    }

    public function getActiveUdmultiStock($productId, $force=false)
    {
        return $this->_getUdmultiStock($productId, $force, true);
    }

    public function getUdmultiStock($productId, $force=false)
    {
        return $this->_getUdmultiStock($productId, $force, false);
    }

    protected function _getUdmultiStock($productId, $force=false, $isActive=false)
    {
        $vCollection = $this->_getMultiVendorData(array($productId), false, $force, $isActive);
        $udmArr = array();
        $qty = 0;
        foreach ($vCollection as $vp) {
            $udmArr[$vp->getVendorId()] = $vp->getStockQty();
        }
        return $udmArr;
    }

    public function getActiveUdmultiAvail($productId, $force=false)
    {
        return $this->_getUdmultiAvail($productId, $force, true);
    }

    public function getUdmultiAvail($productId, $force=false)
    {
        return $this->_getUdmultiAvail($productId, $force, false);
    }

    protected function _getUdmultiAvail($productId, $force=false, $isActive=false)
    {
        $vCollection = $this->_getMultiVendorData(array($productId), false, $force, $isActive);
        $udmArr = array();
        foreach ($vCollection as $vp) {
            $udmArr[$vp->getVendorId()] = array(
                'product_id'  => $vp->getProductId(),
                'stock_qty'   => $vp->getStockQty(),
                'backorders'  => $vp->getData('backorders'),
                'avail_state' => $vp->getData('avail_state'),
                'avail_date'  => $vp->getData('avail_date'),
                'status'      => $vp->getData('status'),
            );
        }
        return $udmArr;
    }

    /**
    * Add or subtract qty from vendor-product stock
    *
    * @param mixed $item
    * @param float $qty use negative to subtract stock
    */
    protected $_uiUseReservedQtyFlag = false;
    public function uiUseVendorReservedQty($vId)
    {
        $_urqVendors = Mage::getSingleton('udropship/source')->getVendorsColumn('use_reserved_qty');
        return $this->uiUseReservedQty() && !empty($_urqVendors[$vId]);
    }
    public function uiUseReservedQty($flag=null)
    {
        $result = $this->_uiUseReservedQtyFlag;
        if (!is_null($flag)) {
            $this->_uiUseReservedQtyFlag = $flag;
        }
        return $result;
    }
    protected $_uiUseStockQtyFlag = true;
    public function uiUseStockQty($flag=null)
    {
        $result = $this->_uiUseStockQtyFlag;
        if (!is_null($flag)) {
            $this->_uiUseStockQtyFlag = $flag;
        }
        return $result;
    }
    public function updateItemStock($item, $qty, $transaction=null)
    {
        $pId = $item->getProductId();
        $vId = $item->getUdropshipVendor();
        if (!$vId && $item->getOrderItem()) {
            $vId = $item->getOrderItem()->getUdropshipVendor();
        }

        if (!$pId || !$vId) {
            // should never happen
            return;
            Mage::throwException(Mage::helper('udropship')->__('Invalid data: vendor_id=%s, product_id=%s', $vId, $pId));
        }

        $v = Mage::helper('udropship')->getVendor($vId);
        if ($v->getStockcheckMethod()) {
            return; // custom stock notification used
        }

        $collection = Mage::getModel('udropship/vendor_product')->getCollection()
            ->addVendorFilter($vId)
            ->addProductFilter($pId);

        if ($collection->count()!==1) {
            // for now silent fail, if the vendor-product association was deleted after order
            return;
            Mage::throwException(Mage::helper('udropship')->__('Failed to update vendor stock: vendor is not associated with this item (%s)', $item->getSku()));
        }

        $totMethod = Mage::getStoreConfig('udropship/stock/total_qty_method');
        foreach ($collection as $vp) {
            if (is_null($vp->getStockQty())) {
                continue;
            }
            if ($this->uiUseStockQty()) {
                $vp->setStockQty($vp->getStockQty()+$qty);
            }
            if ($this->uiUseVendorReservedQty($vId)) {
                $vp->setReservedQty(max($vp->getReservedQty()-$qty, 0));
            }
            if ($transaction) {
                $transaction->addObject($vp);
            } else {
                $vp->save();
            }
            $oItem = $item->getOrderItem();
            $pId = $item->getProductId();
            if (!$pId && $oItem) {
                $pId = $oItem->getProductId();
            }
            if ($item->getProduct()) {
                $product = $item->getProduct();
            } elseif ($item->getOrderItem() && $item->getOrderItem()->getProduct()) {
                $product = $item->getOrderItem()->getProduct();
            }
            $stockItem = null;
            if (!empty($product)) {
                $stockItem = $product->getStockItem();
            }
            if (!$stockItem && $pId) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pId);
            }
            if ($stockItem) {
                $stockQty = max($stockItem->getQty()+$qty, 0);
                $stockItem->setQty($stockQty)->setIsInStock($stockQty>0);
                if ($transaction) {
                    $transaction->addObject($stockItem);
                } else {
                    $stockItem->save();
                }
            }
        }

        return $this;
    }

    public function updateOrderItemsVendors($orderId, $vendors)
    {
        // load order
        $order = Mage::getModel('sales/order')->load($orderId);

        // load order items
        $items = $order->getAllItems();

        $isUdpo = Mage::helper('udropship')->isModuleActive('udpo');
        // retrieve all order shipments
        if (!$isUdpo) {
            if (!Mage::helper('udropship')->isSalesFlat()) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addAttributeToSelect(array('udropship_vendor', 'total_qty'))
                    ->setOrderFilter($order);
            } else {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($order);
            }
            $shipmentsByVendor = array();
            foreach ($shipments as $s) {
                $s->setOrder($order);
                $shipmentsByVendor[$s->getUdropshipVendor()][] = $s;
            }
        }

        // start save and delete transaction
        $save = Mage::getModel('core/resource_transaction');
        $delete = Mage::getModel('core/resource_transaction');

        $changed = false;
        $vendorIds = array();
        // iterate order items
        foreach ($items as $item) {
            // if no vendor update for the item, continue
            if (empty($vendors[$item->getId()])) {
                continue;
            }
            // get new vendor info
            $v = $vendors[$item->getId()];
            $vId = $v['id'];
            // if vendor didn't change, continue
            if ($vId==$item->getUdropshipVendor()) {
                continue;
            }
            $changed = true;
            // if shipment for the item was generated, collect item and vendor ids
            if (!$isUdpo && !empty($shipmentsByVendor[$item->getUdropshipVendor()])) {
                $vendorIds[$item->getId()] = $vId;
            }
            // calculate item qty to update stock with
            $qty = Mage::helper('udropship')->getItemStockCheckQty($item);
            // update stock for old vendor shipment
            if ($qty) {
                $this->updateItemStock($item, $qty, $save);
            }
            // update order item with new vendor and cost
            $item->setUdropshipVendor($vId);
            $item->setUdmOrigBaseCost($item->getBaseCost());
            if (!is_null($v['cost'])) {
                $item->setBaseCost($v['cost']);
            }
            // update stock for new vendor shipment
            if ($qty) {
                $this->updateItemStock($item, -$qty, $save);
            }
            // add item to save transaction
            $save->addObject($item);

            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    // calculate item qty to update stock with
                    $qty = Mage::helper('udropship')->getItemStockCheckQty($child);
                    // update stock for old vendor shipment
                    if ($qty) {
                        $this->updateItemStock($child, $qty, $save);
                    }
                    // update order item with new vendor and cost
                    $child->setUdropshipVendor($vId);
                    $child->setUdmOrigBaseCost($child->getBaseCost());
                    if (!is_null($v['cost'])) {
                        $child->setBaseCost($v['cost']);
                    }
                    // update stock for new vendor shipment
                    if ($qty) {
                        $this->updateItemStock($child, -$qty, $save);
                    }
                    // add item to save transaction
                    $save->addObject($child);
                }
            }
        }

        $shippedItemIds = array();
        if (!$isUdpo) {
            // in case we'll need to generate new shipments
            $convertor = Mage::getModel('sales/convert_order');

            // clone shipments to avoid affecting the loop by adding a new shipment
            $oldShipments = clone $shipments;
            // iterate shipment items
            foreach ($oldShipments as $oldShipment) {
                $sItems = $oldShipment->getAllItems();
                foreach ($sItems as $sItem) {
                    $orderItemId = $sItem->getOrderItemId();
                    // no changes needed for this order item
                    if (empty($vendorIds[$orderItemId])) {
                        continue;
                    }
                    // get new vendor id
                    $vId = $vendorIds[$orderItemId];
                    $vendor = Mage::helper('udropship')->getVendor($vId);
                    // safeguard against changing vendor twice
                    if ($vId==$oldShipment->getUdropshipVendor()) {
                        continue;
                    }
                    // update old shipment
                    $udmOrigBaseCost = $sItem->getOrderItem()->getUdmOrigBaseCost();
                    $baseCost = $sItem->getOrderItem()->getBaseCost();
                    $oldShipment->setTotalCost($oldShipment->getTotalCost()-$sItem->getQty()*$udmOrigBaseCost);
                    $oldShipment->setTotalQty($oldShipment->getTotalQty()-$sItem->getQty());
                    $oldShipment->getItemsCollection()->removeItemByKey($sItem->getId());
                    // if target shipment already exists, use it
                    if (!empty($shipmentsByVendor[$vId])) {
                        $newShipment = current($shipmentsByVendor[$vId]);
                    } else {
                        // otherwise create a new one
                        $shipmentStatus = Mage::getStoreConfig('udropship/vendor/default_shipment_status', $order->getStoreId());
                        if ('999' != $vendor->getData('initial_shipment_status')) {
                            $shipmentStatus = $vendor->getData('initial_shipment_status');
                        }
                        $newShipment = $convertor->toShipment($order)
                            ->setUdropshipVendor($vId)
                            ->setUdropshipStatus($shipmentStatus);
                        // and add it to collection
                        $shipments->addItem($newShipment);
                        $shipmentsByVendor[$vId][] = $newShipment;
                    }
                    // update the new shipment
                    $newShipment->setTotalCost($newShipment->getTotalCost()+$sItem->getQty()*$baseCost);
                    $newShipment->setTotalQty($newShipment->getTotalQty()+$sItem->getQty());
                    $newShipment->setUdropshipMethod($vendors[$orderItemId]['method']);
                    $newShipment->setUdropshipMethodDescription($vendors[$orderItemId]['method_name']);
                    // retrieve shipment items before adding a new one
                    $newShipment->getItemsCollection();
                    // a little hack to force magento add item into shipment items collection
                    $sItemId = $sItem->getId();
                    $sItem->setId(null);
                    $sItem->setBaseCost($baseCost);
                    $newShipment->addItem($sItem);
                    $sItem->setId($sItemId);
                    // remember the shipment to save and send notification
                    $newShipment->setUdmultiSave(true)->setUdmultiSend(true);
                    // old save is in the internal loop to make sure that it's skipped when dup safeguard is triggered
                    $oldShipment->setUdmultiSave(true);
                    $shippedItemIds[] = $orderItemId;
                }
            }
            $sendNotifications = array();
            foreach ($shipments as $s) {
                if (!$s->getUdmultiSave()) {
                    continue;
                }
                if (count($s->getAllItems())>0) {
                    $save->addObject($s);
                    if ($s->getUdmultiSend()) {
                        $sendNotifications[] = $s;
                    }
                } else {
                    // if any shipments/vendors have no more products, delete them
                    $delete->addObject($s);
                }
            }

            // commit transactions
            $save->save();
            $delete->delete();

            $vendorRates = array();
            $shippingMethod = explode('_', $order->getShippingMethod(), 2);
            $shippingDetails = $order->getUdropshipShippingDetails();
            $details = Zend_Json::decode($shippingDetails);
            if (!empty($details) && !empty($shippingMethod[1])) {
                if (!empty($details['methods'][$shippingMethod[1]])) {
                    $vendorRates = &$details['methods'][$shippingMethod[1]]['vendors'];
                } elseif (!empty($details['methods'])) {
                    $vendorRates = &$details['methods'];
                }
            }

            foreach ($vendors as $orderItemId=>$vData) {
                if (in_array($orderItemId, $shippedItemIds)) continue;
                if (empty($vendorRates[$vData['id']])) {
                    list($carrierTitle, $methodTitle) = explode('-', $vData['method_name'], 2);
                    $vendorRates[$vData['id']] = array(
                        'cost'  => 0,
                        'price' => 0,
                        'code'  => $vData['method'],
                        'carrier_title' => @$carrierTitle,
                        'method_title'  => @$methodTitle
                    );
                }
            }

            $order->setUdropshipShippingDetails(Zend_Json::encode($details));
            $order->getResource()->saveAttribute($order, 'udropship_shipping_details');

            // send pending notifications
            foreach ($sendNotifications as $s) {
                Mage::helper('udropship')->sendVendorNotification($s);
            }
            Mage::helper('udropship')->processQueue();
        } else {
            $save->save();
        }

        return $changed;
    }

	public function saveThisVendorProducts($data, $v)
    {
        return $this->_saveVendorProducts($data, false, $v);
    }
    public function saveVendorProducts($data)
    {
        return $this->_saveVendorProducts($data, false);
    }
	public function saveThisVendorProductsPidKeys($data, $v)
    {
        return $this->_saveVendorProducts($data, true, $v);
    }
    public function saveVendorProductsPidKeys($data)
    {
        return $this->_saveVendorProducts($data, true);
    }
    public function setReindexFlag($flag)
    {
        Mage::helper('udmulti/protected')->setReindexFlag($flag);
        return $this;
    }
    protected function _saveVendorProducts($data, $pidKeys=false, $v=null)
    {
        return Mage::helper('udmulti/protected')->saveVendorProducts($data, $pidKeys, $v);
    }

    public function isVendorProductShipping($vendor=null)
    {
        $result = false;
        static $transport;
        if ($transport === null) {
            $transport = new Varien_Object;
        }
        $transport->setEnabled($result);
        Mage::dispatchEvent('udmulti_isVendorProductShipping', array('vendor' => $vendor, 'transport' => $transport));
        return $transport->getEnabled();
    }

    public function getVendorSelect($data)
    {
        $html = '<select name="'.@$data['name'].'" id="'.@$data['id'].'" class="'
            .@$data['class'].'" title="'.@$data['title'].'" '.@$data['extra'].' onchange="try{if (this.selectedIndex>-1) {$(\''.@$data['cost_id'].'\').value=this.options[this.selectedIndex].title}}catch(e){}">';
        if (is_array($data['options'])) {
            foreach ($data['options'] as $vId => $opt) {
                $selectedHtml = $vId == @$data['selected'] ? ' selected="selected"' : '';
                $html .= '<option value="'.$vId.'" title="'.@$opt['cost'].'" '.$selectedHtml.'>'.$this->htmlEscape(@$opt['name']).'</option>';
            }
        }
        $html.= '</select>';
        $html.= '<input type="hidden" name="'.@$data['cost_name'].'" id="'.@$data['cost_id'].'" value="'.@$data['options'][@$data['selected']]['cost'].'" class="'.@$data['cost_class'].'" />';
        return $html;
    }

    public function getAvailState($state, $returnType='code')
    {
        return Mage::getSingleton('udmulti/source')->getAvailState($state, $returnType);
    }
    public function getAvailabilityState($state, $returnType='code')
    {
        return Mage::getSingleton('udmulti/source')->getAvailabilityState($state, $returnType);
    }

    public function getBackorderByAvail($vendorData, $initBackorder)
    {
        reset($vendorData);
        $v = Mage::helper('udropship')->getVendor(key($vendorData));
        $vendorData = current($vendorData);
        if ($initBackorder == Unirgy_DropshipMulti_Model_Source::AVAIL_BACKORDERS_YES_NONOTIFY
            || $initBackorder == Unirgy_DropshipMulti_Model_Source::AVAIL_BACKORDERS_YES_NOTIFY
        ) {
            $yes = false;
            if ($v->getBackorderByAvailability() && is_array($vendorData)) {
                if (@$vendorData['avail_state'] == 'available') {
                    $yes = true;
                } elseif (@$vendorData['avail_state'] == 'to_be_published'
                    && !empty($vendorData['avail_date']) && false === strpos($vendorData['avail_date'], '0000-00-00')
                ) {
                    $nowDate = Mage::app()->getLocale()->date();
                    $nowDate->addDay(Mage::getStoreConfig('udropship/stock/backorder_if_available_in'));
                    $yes = $nowDate->compare($vendorData['avail_date'], Varien_Date::DATETIME_INTERNAL_FORMAT) >= 0;
                }
            }
            $resBackorder = $initBackorder == Unirgy_DropshipMulti_Model_Source::AVAIL_BACKORDERS_YES_NONOTIFY
                ? Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY
                : Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;
            return $yes ? $resBackorder : Mage_CatalogInventory_Model_Stock::BACKORDERS_NO;
        } else {
            return $initBackorder;
        }
    }

    public function getStockItemUdropshipVendor($item)
    {
        $vId = $item->getForcedUdropshipVendor();
        if (!$vId && $item->getProduct()) {
            $vId = $item->getProduct()->getForcedUdropshipVendor();
        }
        if (!$vId) {
            //$vId = Mage::getSingleton('cataloginventory/observer')->getUdropshipVendor();
        }
        return $vId;
    }

    public function attachMultivendorData($products, $isActive, $reload=false)
    {
        $pIds = array();
        foreach ($products as $product) {
            if ($product->hasUdmultiStock() && !$reload || !$product->getId()) {
                if ($product->getStockItem()) {
                    $product->getStockItem()->setUdmultiStock($product->getUdmultiStock());
                    $product->getStockItem()->setUdmultiAvail($product->getUdmultiAvail());
                }
                continue;
            }
            $pIds[] = $product->getId();
        }
        $loadMethod = $isActive ? 'getActiveMultiVendorData' : 'getMultiVendorData';
        $vendorData = Mage::helper('udmulti')->$loadMethod($pIds);
        $gpLoadMethod = $isActive ? 'getActiveMvGroupPrice' : 'getMvGroupPrice';
        $gpData = Mage::helper('udmulti')->$gpLoadMethod($pIds);
        $tpLoadMethod = $isActive ? 'getActiveMvTierPrice' : 'getMvTierPrice';
        $tpData = Mage::helper('udmulti')->$tpLoadMethod($pIds);
        foreach ($products as $product) {
            if ($product->hasUdmultiStock() && !$reload || !$product->getId()) continue;
            $udmData = $udmAvail = $udmStock = array();
            foreach ($vendorData as $vp) {
                if ($vp->getProductId() != $product->getId()) continue;
                $udmGroupPrice = $udmTierPrice = array();
                $udmStock[$vp->getVendorId()] = $vp->getStockQty();
                $udmData[$vp->getVendorId()] = $vp->getData();
                $udmAvail[$vp->getVendorId()] = array(
                    'product_id'  => $vp->getProductId(),
                    'stock_qty'   => $vp->getStockQty(),
                    'backorders'  => $vp->getData('backorders'),
                    'avail_state' => $vp->getData('avail_state'),
                    'avail_date'  => $vp->getData('avail_date'),
                    'status'      => $vp->getData('status'),
                );
                foreach ($gpData as $__gpd) {
                    if ($vp->getProductId() != $__gpd->getProductId() || $vp->getVendorId() != $__gpd->getVendorId()) continue;
                    $udmGroupPrice[] = $__gpd->getData();
                }
                foreach ($tpData as $__tpd) {
                    if ($vp->getProductId() != $__tpd->getProductId() || $vp->getVendorId() != $__tpd->getVendorId()) continue;
                    $udmTierPrice[] = $__tpd->getData();
                }
                $udmData[$vp->getVendorId()]['group_price'] = $udmGroupPrice;
                $udmData[$vp->getVendorId()]['tier_price'] = $udmTierPrice;
            }
            $product->setMultiVendorData($udmData);
            $product->setAllMultiVendorData($udmData);
            $product->setUdmultiStock($udmStock);
            $product->setUdmultiAvail($udmAvail);
            if ($product->getStockItem()) {
                $product->getStockItem()->setUdmultiStock($udmStock);
                $product->getStockItem()->setUdmultiAvail($udmAvail);
            }
            if ($isActive && Mage::getStoreConfigFlag('udropship/stock/hide_out_of_stock')) {
                $vendorsToHide = array();
                foreach ($udmData as $vId=>$dummy) {
                    if (!Mage::helper('udmulti')->isSalableByVendorData($product, $vId, $dummy)) {
                        $vendorsToHide[$vId] = $vId;
                    }
                }
                if (!empty($vendorsToHide)) {
                    foreach ($vendorsToHide as $vId) {
                        unset($udmStock[$vId], $udmData[$vId], $udmAvail[$vId]);
                    }
                    $product->setMultiVendorData($udmData);
                    $product->setUdmultiStock($udmStock);
                    $product->setUdmultiAvail($udmAvail);
                    if ($product->getStockItem()) {
                        $product->getStockItem()->setUdmultiStock($udmStock);
                        $product->getStockItem()->setUdmultiAvail($udmAvail);
                    }
                }
            }
            if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $minPrice = PHP_INT_MAX;
                $minVendorId = 0;
                foreach ($udmData as $vp) {
                    if (null !== $vp['vendor_price']) {
                        Mage::helper('udmultiprice')->useVendorPrice($product, $vp);
                        if ($minPrice>$product->getFinalPrice()) {
                            $minPrice = min($minPrice, $product->getFinalPrice());
                            $minVendorId = $vp['vendor_id'];
                        }
                        Mage::helper('udmultiprice')->revertVendorPrice($product);
                    }
                }
                if ($minPrice == PHP_INT_MAX) {
                    $minPrice = $product->getFinalPrice();
                }
                $product->setUdmultiBestVendor($minVendorId);
                $product->setUdmultiBestPrice($minPrice);
            }
        }
        return $this;
    }
    
    public function verifyDecisionCombination($items, $combination)
    {
        foreach ($items as $item) {
            if ($item->getHasChildren() && !$item->isShipSeparately()) {
                $children = $item->getChildren() ? $item->getChildren() : $item->getChildrenItems();
                $vId = null;
                foreach ($children as $child) {
                    foreach ($combination as $cmb) {
                        if ($child->getProductId()==$cmb['p']) {
                            if ($vId === null) {
                                $vId = $cmb['v'];
                            }
                            if ($vId != $cmb['v']) {
                                return false;
                            }
                            break;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function getDefaultMvStatus($storeId=null)
    {
        return Mage::getStoreConfig('udropship/stock/default_multivendor_status', $storeId);
    }

    public function getBackorders($vendorData, $initBackorder)
    {
        $initBackorder = $this->getNativeBackorders($initBackorder);
        reset($vendorData);
        $vendorData = current($vendorData);
        $backorders = @$vendorData['backorders'] == Unirgy_DropshipMulti_Model_Source::BACKORDERS_USE_CONFIG
            ? $initBackorder
            : @$vendorData['backorders'];
        return $backorders;
    }
    public function isSalableByVendor($product, $vId)
    {
        $product->setForcedUdropshipVendor($vId);
        $result = $product->isSalable();
        $product->unsForcedUdropshipVendor();
        return $result;
    }
    public function isQtySalableByFullVendorData($qty, $product, $vId, $mvData, $forcedStockQty=false)
    {
        $_mv = @$mvData[$vId];
        if (empty($_mv) || !is_array($_mv)) {
            return false;
        }
        return $this->_isSalableByVendorData($product, $vId, $_mv, $qty, $forcedStockQty);
    }
    public function isSalableByFullVendorData($product, $vId, $mvData, $forcedStockQty=false)
    {
        $_mv = @$mvData[$vId];
        if (empty($_mv) || !is_array($_mv)) {
            return false;
        }
        return $this->_isSalableByVendorData($product, $vId, $_mv, null, $forcedStockQty);
    }
    public function isQtySalableByVendorData($qty, $product, $vId, $mvData, $forcedStockQty=false)
    {
        return $this->_isSalableByVendorData($product, $vId, $mvData, $qty, $forcedStockQty);
    }
    public function isSalableByVendorData($product, $vId, $mvData, $forcedStockQty=false)
    {
        return $this->_isSalableByVendorData($product, $vId, $mvData, null, $forcedStockQty);
    }
    protected function _isSalableByVendorData($product, $vId, $mvData, $qty=null, $forcedStockQty=false)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $stockItem = $product->getStockItem();
        } elseif ($product instanceof Mage_CatalogInventory_Model_Stock_Item) {
            $stockItem = $product;
        } elseif (is_numeric($product)) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $stockItem = $stockItem->getId() ? $stockItem : null;
        } elseif (is_array($product)) {
            $stockItem = $product;
        }
        $stockQty = $this->getQtyFromMvData($mvData, $forcedStockQty);
        $qtyCheck = $qty === null ? $stockQty>$this->getNativeMinQty($stockItem) : $stockQty>=$qty;
        $salableCheck = $qtyCheck || ($stockItem && $this->getBackorders(array($vId=>$mvData), $this->getNativeBackorders($stockItem)));
        return $salableCheck;
    }

    public function getQtyFromFullMvData($mvData, $vId, $forcedQty=false)
    {
        $_mv = @$mvData[$vId];
        if ($forcedQty===false
            && (empty($_mv)
                || !is_array($_mv)
                || !array_key_exists('stock_qty', $_mv)
        )) {
            return 0;
        }
        if (@$_mv['status']<=0) return 0;
        return $this->_getQtyFromMvData((array)$_mv, $forcedQty);
    }
    public function getQtyFromMvData($mvData, $forcedQty=false)
    {
        return $this->_getQtyFromMvData((array)$mvData, $forcedQty);
    }
    protected function _getQtyFromMvData($mvData, $forcedQty=false)
    {
        if ($forcedQty===false
            && (empty($mvData)
                || !is_array($mvData)
                || !array_key_exists('stock_qty', $mvData)
        )) {
            return 0;
        }
        if (@$mvData['status']<=0) return 0;
        $qty = $forcedQty !== false ? $forcedQty : $mvData['stock_qty'];
        $qtyUsed = @$mvData['__qty_used'];
        return $qty === null ? 10000 : $qty-$qtyUsed;
    }

    public function getNativeBackorders($stockItem)
    {
        if ($stockItem instanceof Varien_Object) {
            if ($stockItem->getUseConfigBackorders()) {
                return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);
            }
            return $stockItem->getData('backorders');
        } elseif (is_array($stockItem)
            && array_key_exists('backorders', $stockItem)
            && array_key_exists('use_config_backorders', $stockItem)
        ) {
            if ($stockItem['use_config_backorders']) {
                return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS);
            }
            return $stockItem['backorders'];
        } elseif (is_numeric($stockItem)) {
            return $stockItem;
        } else {
            return Mage_CatalogInventory_Model_Stock::BACKORDERS_NO;
        }
    }

    public function getNativeMinQty($stockItem)
    {
        if ($stockItem instanceof Varien_Object) {
            if ($stockItem->getUseConfigMinQty()) {
                return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);
            }
            return $stockItem->getData('min_qty');
        } elseif (is_array($stockItem)
            && array_key_exists('min_qty', $stockItem)
            && array_key_exists('use_config_min_qty', $stockItem)
        ) {
            if ($stockItem['use_config_min_qty']) {
                return (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY);
            }
            return $stockItem['min_qty'];
        } elseif (is_numeric($stockItem)) {
            return $stockItem;
        } else {
            return 0;
        }
    }

    public function getCreateOrderItemsGridFile()
    {
        if (
            Mage::helper('udropship')->compareMageVer('1.7.0.0', '1.12.0.0')
        ) {
            return 'udmulti/order/create/1700/items_grid.phtml';
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.6.0.0', '1.11.0.0')
        ) {
            return 'udmulti/order/create/1600/items_grid.phtml';
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.5.0.0', '1.10.0.0')
        ) {
            return 'udmulti/order/create/1500/items_grid.phtml';
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.4.1.0', '1.8.0.0')
        ) {
            return 'udmulti/order/create/1410/items_grid.phtml';
        }
    }
    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }
        return new Zend_Db_Expr($expression);
    }
    public function getDatePartSql($date)
    {
        return new Zend_Db_Expr(sprintf('DATE(%s)', $date));
    }

    public function isQty($item)
    {
        $typeId = $item->getTypeId();
        if ($productTypeId = $item->getProductTypeId()) {
            $typeId = $productTypeId;
        }
        return Mage::helper('catalogInventory')->isQty($typeId);
    }

}
