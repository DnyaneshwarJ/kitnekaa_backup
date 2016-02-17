<?php

class Unirgy_DropshipMulti_Model_Observer
{
    public function isQty($item)
    {
        return Mage::helper('udmulti')->isQty($item);
    }
    public function catalog_product_is_salable_after($observer)
    {
        $salable = $observer->getSalable();
        $product = $observer->getProduct();
        if ($product->getForcedUdropshipVendor() && !$product->isComposite() && $product->getStockItem()) {
            $salable->setIsSalable($salable->getIsSalable() && $product->getStockItem()->getIsInStock());
        }
    }
    public function udropship_stock_item_getIsInStock($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $item = $observer->getEvent()->getItem();
        $vars = $observer->getEvent()->getVars();
        $hlpm = Mage::helper('udmulti');

        if (!$this->isQty($item)) return;

        $avail = $item->getUdmultiAvail();
        if ($item->getUdmultiStock()) {
            $result = false;
            if (($tvId = $hlpm->getStockItemUdropshipVendor($item))) {
                $vQty = $item->getUdmultiStock($tvId);
                $result = $hlpm->isSalableByFullVendorData($item, $tvId, $avail, $vQty);
            } else {
                $result = false;
                foreach ($item->getUdmultiStock() as $vId=>$vQty) {
                    if ($hlpm->isSalableByFullVendorData($item, $vId, $avail, $vQty)) {
                        $result = true;
                        break;
                    }
                }
            }
#Mage::log(__METHOD__.': '.$result);
            $vars['result'] = $result;
        }
    }

    public function udropship_stock_item_checkQty($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $item = $observer->getEvent()->getItem();
        $qty = $observer->getEvent()->getQty();
        $vars = $observer->getEvent()->getVars();
        $hlpm = Mage::helper('udmulti');

        if (!$this->isQty($item)) return;
        
        $avail = $item->getUdmultiAvail();
        if ($item->getUdmultiStock()) {
            $result = false;
            if (($tvId = $hlpm->getStockItemUdropshipVendor($item))) {
                $vQty = $item->getUdmultiStock($tvId);
                $result = $hlpm->isQtySalableByFullVendorData($qty, $item, $tvId, $avail, $vQty);
            } else {
                $result = false;
                foreach ($item->getUdmultiStock() as $vId=>$vQty) {
                    if ($hlpm->isQtySalableByFullVendorData($qty, $item, $vId, $avail, $vQty)) {
                        $result = true;
                        break;
                    }
                }
            }
#Mage::log(__METHOD__.': '.$result);
            if (!$result) {
                switch ($item->getBackorders()) {
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                        $result = true;
                        break;
                    default:
                        return false;
                        break;
                }
            }
#Mage::log(__METHOD__.': '.$result);
            if (!$result) {
                switch ($item->getBackorders()) {
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                    case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                        $result = true;
                        break;
                    default:
                        return false;
                        break;
                }
            }
            $vars['result'] = $result;
        }
    }

    public function udropship_stock_item_getBackorders($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $item = $observer->getEvent()->getItem();
        $vars = $observer->getEvent()->getVars();

        if (!$this->isQty($item)) return;

        $avail = $item->getUdmultiAvail();
        if ($item->hasUdmultiStock()) {
            $backorders = $vars['backorders'];
            if (($tvId = Mage::helper('udmulti')->getStockItemUdropshipVendor($item))) {
                $backorders = Mage::helper('udmulti')->getBackorders(
                    array($tvId => $item->getUdmultiAvail($tvId)),
                    $backorders
                );
            } else {
                foreach ($item->getUdmultiStock() as $vId=>$vQty) {
                    if (@$avail[$vId]['status']>0) {
                        $_backorders = Mage::helper('udmulti')->getBackorders(
                            array($vId => @$avail[$vId]),
                            $backorders
                        );
                        if (in_array($_backorders, array(
                            Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY,
                            Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY
                        ))) {
                            $backorders = $_backorders;
                            break;
                        }
                    }
                }
            }
            $vars['backorders'] = $backorders;
        }
    }

    public function udropship_stock_item_getQty($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $item = $observer->getEvent()->getItem();
        $vars = $observer->getEvent()->getVars();
        $method = Mage::getStoreConfig('udropship/stock/total_qty_method');
        $hlpm = Mage::helper('udmulti');

        if (!$this->isQty($item)) return;

        $avail = $item->getUdmultiAvail();
        if ($item->getUdmultiStock()) {
            if (($tvId = Mage::helper('udmulti')->getStockItemUdropshipVendor($item))) {
                $vQty = $item->getUdmultiStock($tvId);
                $qty = $hlpm->getQtyFromFullMvData($avail, $tvId, $vQty);
            } else {
                $qty = 0;
                foreach ($item->getUdmultiStock() as $vId=>$vQty) {
                    $vQty = $hlpm->getQtyFromFullMvData($avail, $vId, $vQty);
                    if ($method=='sum') {
                        $qty += max($vQty, 0);
                    } else {
                        $qty = max($qty, $vQty);
                    }
                }
            }
#Mage::log(__METHOD__.': '.$qty);
            $vars['qty'] = $qty;
        }
    }

    public function udropship_stock_item_checkQuoteItemQty($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
#        $item = $observer->getEvent()->getItem();
#        $vars = $observer->getEvent()->getVars();
#var_dump($vars['result']);
#        $vars['result'] = false;
#echo __METHOD__; exit;
    }

    public function udropship_shipment_assign_vendor_skus($observer)
    {
    }
    public function udpo_po_assign_vendor_skus($observer)
    {
    }
    public function udropship_po_add_vendor_skus($observer)
    {
        $po = $observer->getEvent()->getPo();
        Mage::helper('udmulti/protected')->udropship_po_add_vendor_skus($po);
    }

    public function attachMultivendorData($products, $isActive, $reload=false)
    {
        Mage::helper('udmulti')->attachMultivendorData($products, $isActive, $reload);
        return $this;
    }

    public function catalog_product_load_after_front($observer)
    {
        $this->_catalog_product_load_after($observer, true);
    }
    public function catalog_product_load_after_admin($observer)
    {
        $this->_catalog_product_load_after($observer, false);
    }
    public function catalog_product_load_after($observer) {}
    protected function _catalog_product_load_after($observer, $isActive)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        if (!$product instanceof Mage_Catalog_Model_Product || $product->getData('__skip_udmulti_load')) {
            return;
        }
        $this->attachMultivendorData(array($product), $isActive);
    }
    
    public function catalog_product_collection_load_after_front($observer)
    {
        $this->_catalog_product_collection_load_after($observer, true);
    }
    public function catalog_product_collection_load_after_admin($observer)
    {
        $this->_catalog_product_collection_load_after($observer, false);
    }
    public function catalog_product_collection_load_after($observer) {}
    protected function _catalog_product_collection_load_after($observer, $isActive)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $pCollection = $observer->getEvent()->getCollection();
        if ($pCollection->getFlag('skip_udmulti_load') || !$this->_isUdmultiLoadToCollection) return;
        $this->attachMultivendorData($pCollection, $isActive);
    }

    public function sales_quote_item_collection_products_after_load_front($observer)
    {
        $this->_sales_quote_item_collection_products_after_load($observer, true);
    }
    public function sales_quote_item_collection_products_after_load_admin($observer)
    {
        $this->_sales_quote_item_collection_products_after_load($observer, false);
    }
    public function sales_quote_item_collection_products_after_load($observer) {}
    protected function _sales_quote_item_collection_products_after_load($observer, $isActive)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $pCollection = $observer->getEvent()->getProductCollection();
        $this->attachMultivendorData($pCollection, $isActive);
    }

    public function sales_quote_item_set_product($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
#Mage::log(__METHOD__);
        $p = $observer->getEvent()->getProduct();
        $item = $observer->getEvent()->getQuoteItem();
        $vcKey = sprintf('multi_vendor_data/%s/vendor_cost', $item->getUdropshipVendor());
        if (($vc = $p->getData($vcKey)) && $vc>0) {
            $item->setBaseCost($vc);
            if (($parent = $item->getParentItem()) && $parent->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                $parent->setBaseCost($vc);
            }
        }
    }

    public function udropship_carrier_collect_before($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $request = $observer->getRequest();
        $hasItem = false;
        $allFree = true;
        foreach ($request->getAllItems() as $item) {
            $hasItem = true;
            $p = $item->getProduct();
            $vcKey = sprintf('multi_vendor_data/%s/freeshipping', $item->getUdropshipVendor());
            if (($vc = $p->getData($vcKey))) {
                $item->setFreeShipping($vc);
                if (($parent = $item->getParentItem()) && $parent->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $pProd = $parent->getProduct();
                    if (($vc = $pProd->getData($vcKey))) {
                        $item->setFreeShipping($vc);
                    }
                }
            }
            if (!$item->getFreeShipping()) {
                $allFree = false;
            }
        }
        if ($allFree && $hasItem) {
            $observer->getAddress()->setFreeShipping(true);
        }
    }

    public function catalog_product_prepare_save($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        Mage::helper('udmulti/protected')->catalog_product_prepare_save($request, $product);

    }

    public function udropship_stock_item_save_before($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }

        $hlpm = Mage::helper('udmulti');
        $method = Mage::getStoreConfig('udropship/stock/total_qty_method');
        $item = $observer->getEvent()->getItem();
        $product = $item->getProductObject();

        if (!$this->isQty($item)) return;

        $qty = null;
        $isInStock = false;
        if ($product && $product->getUpdateUdmultiVendors()) {
            $data = (array)$product->getUpdateUdmultiVendors();
            if ($data) {
                $qty = 0;
                foreach ($data['vendor_stock'] as $vId=>$vQty) {
                    if (@$data['avail'][$vId]['status']>0) {
                        $vQty = is_null($vQty) || $vQty==='' ? 10000 : $vQty;
                        if ($method=='sum') {
                            $qty += max($vQty, 0);
                        } else {
                            $qty = max($qty, $vQty);
                        }
                        $isInStock = $isInStock || $qty>0 || $hlpm->getBackorders(array($vId=>$data['avail'][$vId]), $item->getBackorders());
                    }
                }
            }
        } else {
            $udm = Mage::helper('udmulti')->getUdmultiStock($item->getProductId());
            $avail = Mage::helper('udmulti')->getUdmultiAvail($item->getProductId());
            $item->setUdmultiSet($udm);
            if ($udm) {
                foreach ($udm as $vId=>$vQty) {
                    if (@$avail[$vId]['status']>0) {
                        $vQty = is_null($vQty) || $vQty==='' ? 10000 : $vQty;
                        if ($method=='sum') {
                            $qty += max($vQty, 0);
                        } else {
                            $qty = max($qty, $vQty);
                        }
                        $isInStock = $isInStock || $qty>0 || $hlpm->getBackorders(array($vId=>$avail[$vId]), $item->getBackorders());
                    }
                }
            }
        }
        if (!is_null($qty)) {
            $item->setIsInStock($isInStock);
            $item->setQty($qty);
        }
    }

    public function cataloginventory_stock_item_save_after($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return;
        }
        $item = $observer->getEvent()->getItem();
        $product = $item->getProductObject();

        if (!$product || !$product->hasUdmultiStock()) {
            return;
        }
        $data = (array)$product->getUpdateUdmultiVendors();

        Mage::getSingleton('cataloginventory/stock_status')
            ->updateStatus($product->getId());
    }

    public function catalog_product_save_after($observer)
    {
        $product = $observer->getEvent()->getProduct();

        $data = $product->getUpdateUdmultiVendors();
        if (!(empty($data['delete']) && empty($data['insert']) && empty($data['update']))) {

            $res = Mage::getSingleton('core/resource');
            $write = $res->getConnection('udropship_write');
            $table = $res->getTableName('udropship/vendor_product');

            if (!empty($data['delete'])) {
                $write->delete($table, $write->quoteInto('vendor_product_id in (?)', $data['delete']));
            }

            if (!empty($data['insert'])) {
                foreach ($data['insert'] as $v) {
                    $v['product_id'] = $product->getId();
                    if (!array_key_exists('status', $v)) {
                        $v['status'] = Mage::helper('udmulti')->getDefaultMvStatus();
                    }
                    $_v = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($table, $v);
                    $write->insert($table, $_v);
                    $lastUvpId = $write->lastInsertId();
                    if (isset($v['group_price']) && is_array($v['group_price'])) {
                        $gpTable = $res->getTableName('udmulti/group_price');
                        foreach ($v['group_price'] as $gpKey => $gp) {
                            if ($gpKey=='$ROW' || $gpKey=='$$ROW') continue;
                            $gp['vendor_id'] = $v['vendor_id'];
                            $gp['product_id'] = $product->getId();
                            $gp['all_groups'] = 0;
                            if ($gp['customer_group_id'] == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
                                $gp['all_groups'] = 1;
                                $gp['customer_group_id'] = 0;
                            }
                            $gp['vendor_product_id'] = $lastUvpId;
                            $insertGroup = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($gpTable, $gp);
                            $write->insert($gpTable, $insertGroup);
                        }
                    }
                    if (isset($v['tier_price']) && is_array($v['tier_price'])) {
                        $tpTable = $res->getTableName('udmulti/tier_price');
                        foreach ($v['tier_price'] as $tpKey => $tp) {
                            if ($tpKey=='$ROW' || $tpKey=='$$ROW') continue;
                            $tp['vendor_id'] = $v['vendor_id'];
                            $tp['product_id'] = $product->getId();
                            $tp['all_groups'] = 0;
                            if ($tp['customer_group_id'] == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
                                $tp['all_groups'] = 1;
                                $tp['customer_group_id'] = 0;
                            }
                            $tp['vendor_product_id'] = $lastUvpId;
                            $insertGroup = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($tpTable, $tp);
                            $write->insert($tpTable, $insertGroup);
                        }
                    }
                }
            }

            if (!empty($data['update'])) {
                foreach ($data['update'] as $id=>$v) {
                    $_v = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($table, $v);
                    $write->update($table, $_v, 'vendor_product_id='.(int)$id);
                    if (isset($v['group_price']) && is_array($v['group_price'])) {
                        $gpTable = $res->getTableName('udmulti/group_price');
                        $_gpValIds = array();
                        foreach ($v['group_price'] as $gpKey => $gp) {
                            if ($gpKey=='$ROW' || $gpKey=='$$ROW') continue;
                            if (isset($gp['value_id'])) $_gpValIds[] = $gp['value_id'];
                        }
                        $gpDelCond = array('vendor_id=?'=>$v['vendor_id'],'product_id=?'=>$product->getId());
                        if ($_gpValIds) {
                            $gpDelCond['value_id not in (?)']=$_gpValIds;
                        }
                        $write->delete($gpTable, $gpDelCond);
                        foreach ($v['group_price'] as $gpKey => $gp) {
                            if ($gpKey=='$ROW' || $gpKey=='$$ROW') continue;
                            $gp['vendor_id'] = $v['vendor_id'];
                            $gp['product_id'] = $product->getId();
                            $gp['all_groups'] = 0;
                            if ($gp['customer_group_id'] == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
                                $gp['all_groups'] = 1;
                                $gp['customer_group_id'] = 0;
                            }
                            $gp['vendor_product_id'] = $id;
                            $insertGroup = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($gpTable, $gp);
                            if (!empty($gp['value_id'])) {
                                $write->update($gpTable, $insertGroup, 'value_id='.(int)$gp['value_id']);
                            } else {
                                $write->insert($gpTable, $insertGroup);
                            }
                        }
                    }
                    if (isset($v['tier_price']) && is_array($v['tier_price'])) {
                        $tpTable = $res->getTableName('udmulti/tier_price');
                        $_tpValIds = array();
                        foreach ($v['tier_price'] as $tpKey => $tp) {
                            if ($tpKey=='$ROW' || $tpKey=='$$ROW') continue;
                            if (isset($tp['value_id'])) $_tpValIds[] = $tp['value_id'];
                        }
                        $tpDelCond = array('vendor_id=?'=>$v['vendor_id'],'product_id=?'=>$product->getId());
                        if ($_tpValIds) {
                            $tpDelCond['value_id not in (?)']=$_tpValIds;
                        }
                        $write->delete($tpTable, $tpDelCond);
                        foreach ($v['tier_price'] as $tpKey => $tp) {
                            if ($tpKey=='$ROW' || $tpKey=='$$ROW') continue;
                            $tp['vendor_id'] = $v['vendor_id'];
                            $tp['product_id'] = $product->getId();
                            $tp['all_groups'] = 0;
                            if ($tp['customer_group_id'] == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
                                $tp['all_groups'] = 1;
                                $tp['customer_group_id'] = 0;
                            }
                            $tp['vendor_product_id'] = $id;
                            $insertTier = Mage::getResourceSingleton('udropship/helper')->myPrepareDataForTable($tpTable, $tp);
                            if (!empty($tp['value_id'])) {
                                $write->update($tpTable, $insertTier, 'value_id='.(int)$tp['value_id']);
                            } else {
                                $write->insert($tpTable, $insertTier);
                            }
                        }

                    }
                }
            }
        }
        Mage::getSingleton('cataloginventory/observer')->saveInventoryData($observer);
    }

    public function sales_order_item_save_before($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return $this;
        }
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
        if (!$item->getId() && empty($children)) {
            Mage::helper('udmulti')->uiUseReservedQty(true);
            Mage::helper('udmulti')->updateItemStock($item, -$item->getQtyOrdered());
            Mage::helper('udmulti')->uiUseReservedQty(false);
        }
        return $this;
    }

    public function sales_order_shipment_save_after($observer)
    {
        $shipment = $observer->getShipment();
        $oldStatus = $shipment->getOrigData('udropship_status');
        $newStatus = $shipment->getData('udropship_status');
        $this->processShipmentStatusChange($shipment, $oldStatus, $newStatus);
    }

    public function udropship_shipment_status_save_after($observer)
    {
        $shipment = $observer->getShipment();
        $oldStatus = $observer->getOldStatus();
        $newStatus = $observer->getNewStatus();
        $this->processShipmentStatusChange($shipment, $oldStatus, $newStatus);
    }

    public function processShipmentStatusChange($shipment, $oldStatus, $newStatus)
    {
        $masStatus = array(
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED,
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED,
            Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED
        );
        $oldInMasStatus = in_array($oldStatus, $masStatus);
        $newInMasStatus = in_array($newStatus, $masStatus);
        if ($oldInMasStatus != $newInMasStatus) {
            Mage::helper('udmulti')->uiUseReservedQty(true);
            Mage::helper('udmulti')->uiUseStockQty(false);
            $parentItems = array();
            foreach ($shipment->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                $children = $oItem->getChildrenItems() ? $oItem->getChildrenItems() : $oItem->getChildren();
                if ($children) {
                    $parentItems[$oItem->getId()] = $item;
                } else {
                    $qty = max(1, $item->getQty());
                    $oParent = $oItem->getParentItem();
                    if ($oParent && $parentItems[$oParent->getId()]) {
                        $qty *= $parentItems[$oParent->getId()]->getQty();
                    }
                    if ($newInMasStatus) {
                        if ($item->getIsReserved()) {
                            Mage::helper('udmulti')->updateItemStock($item, $qty);
                            $item->setIsReserved(0);
                            $item->getResource()->saveAttribute($item, 'is_reserved');
                        }
                    } else {
                        if (!$item->getIsReserved()) {
                            Mage::helper('udmulti')->updateItemStock($item, -$qty);
                            $item->setIsReserved(1);
                            $item->getResource()->saveAttribute($item, 'is_reserved');
                        }
                    }
                }
            }
            Mage::helper('udmulti')->uiUseReservedQty(false);
            Mage::helper('udmulti')->uiUseStockQty(true);
        }
    }

    public function sales_order_item_cancel($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return $this;
        }
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty) {
            Mage::helper('udmulti')->updateItemStock($item, $qty);
        }
        return $this;
    }

    public function sales_creditmemo_item_save_after($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return $this;
        }
        $item = $observer->getEvent()->getCreditmemoItem();
        if ($item->getId() && $item->getProductId() && $item->getBackToStock()) {
            Mage::helper('udmulti')->uiUseReservedQty(true);
            Mage::helper('udmulti')->updateItemStock($item, $item->getQty());
            Mage::helper('udmulti')->uiUseReservedQty(false);
        }
        return $this;
    }

    public function adminhtml_version($observer)
    {
        Mage::helper('udropship')->addAdminhtmlVersion('Unirgy_DropshipMulti');
    }

    public function udropship_adminhtml_vendor_edit_prepare_form($observer)
    {
        $id = $observer->getEvent()->getId();
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('vendor_form');

        /*
        if (Mage::getStoreConfig('udropship/stock/backorder_by_availability')) {
            $fieldset->addField('backorder_by_availability', 'select', array(
                'name'      => 'backorder_by_availability',
                'label'     => Mage::helper('udropship')->__('Allow Backorder by Availability State/Date'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
            ));
        }
        if (Mage::getStoreConfig('udropship/stock/use_reserved_qty')) {
            $fieldset->addField('use_reserved_qty', 'select', array(
                'name'      => 'use_reserved_qty',
                'label'     => Mage::helper('udropship')->__('Track Reserved Qty'),
                'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
            ));
        }
        */
    }
    
    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }

    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        if (!Mage::helper('udropship')->isUdmultiActive()) return;
        Mage::getConfig()->setNode('global/models/cataloginventory_mysql4/rewrite/stock', 'Unirgy_DropshipMulti_Model_Mysql4_Stock');
        Mage::getConfig()->setNode('global/models/cataloginventory/rewrite/observer', 'Unirgy_DropshipMulti_Model_InventoryObserver');
        if (Mage::helper('udropship')->isEE()) {
        Mage::getConfig()->setNode('global/models/enterprise_cataloginventory/rewrite/index_observer', 'Unirgy_DropshipMulti_Model_EeInventoryObserver');
        }
        //Mage::getConfig()->setNode('global/models/cataloginventory/rewrite/source_backorders', 'Unirgy_DropshipMulti_Model_SourceBackorders');
        Mage::getConfig()->setNode('global/models/adminhtml/rewrite/sales_order_create', 'Unirgy_DropshipMulti_Model_AdminOrderCreate');
        if (Mage::helper('udropship')->isEE()
            && Mage::helper('udropship')->compareMageVer('1.8.0.0', '1.13.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/enterprise_cataloginventory_resource/rewrite/indexer_stock_default', 'Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Default');
            Mage::getConfig()->setNode('global/models/enterprise_bundle_resource/rewrite/indexer_stock', 'Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Bundle');
            Mage::getConfig()->setNode('global/models/cataloginventory_resource/rewrite/indexer_stock_configurable', 'Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Configurable');
            Mage::getConfig()->setNode('global/models/cataloginventory_resource/rewrite/indexer_stock_grouped', 'Unirgy_DropshipMulti_Model_StockIndexer_EE11300_Grouped');

        } elseif (
            Mage::helper('udropship')->compareMageVer('1.6.0.0', '1.11.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/cataloginventory_resource/rewrite/indexer_stock_default', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1620_Default');
            Mage::getConfig()->setNode('global/models/cataloginventory_resource/rewrite/indexer_stock_grouped', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1620_Grouped');
            Mage::getConfig()->setNode('global/models/cataloginventory_resource/rewrite/indexer_stock_configurable', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1620_Configurable');
            Mage::getConfig()->setNode('global/models/bundle_resource/rewrite/indexer_stock', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1620_Bundle');
        } elseif (
            Mage::helper('udropship')->compareMageVer('1.4.1.0', '1.8.0.0')
        ) {
            Mage::getConfig()->setNode('global/models/cataloginventory_mysql4/rewrite/indexer_stock_default', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1410_Default');
            Mage::getConfig()->setNode('global/models/cataloginventory_mysql4/rewrite/indexer_stock_grouped', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1410_Grouped');
            Mage::getConfig()->setNode('global/models/cataloginventory_mysql4/rewrite/indexer_stock_price_configurable', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1410_Configurable');
            Mage::getConfig()->setNode('global/models/bundle_mysql4/rewrite/indexer_stock', 'Unirgy_DropshipMulti_Model_StockIndexer_CE1410_Bundle');
        }
    }

    protected $_isUdmultiLoadToCollection=true;
    public function turnOffUdmultiLoadToCollection($observer)
    {
        $this->_isUdmultiLoadToCollection=false;
    }
    public function turnOnUdmultiLoadToCollection($observer)
    {
        $this->_isUdmultiLoadToCollection=true;
    }
}
