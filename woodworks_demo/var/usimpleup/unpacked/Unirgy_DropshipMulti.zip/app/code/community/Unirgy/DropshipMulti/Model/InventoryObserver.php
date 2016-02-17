<?php

class Unirgy_DropshipMulti_Model_InventoryObserver extends Mage_CatalogInventory_Model_Observer
{
    protected $_udmultiQuoteItem;
    public function getUdmultiQuoteItem()
    {
        return $this->_udmultiQuoteItem;
    }
    public function getUdropshipVendor()
    {
        return $this->_udmultiQuoteItem && $this->_udmultiQuoteItem->getUdropshipVendor()
            ? $this->_udmultiQuoteItem->getUdropshipVendor() : null;
    }
    protected function _getProductQtyForCheck($productId, $itemQty)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::_getProductQtyForCheck($productId, $itemQty);
        }
        $qty = $itemQty;
        $pidKey = $this->getUdropshipVendor() ? $this->getUdropshipVendor().'-'.$productId : $productId;
        if (isset($this->_checkedProductsQty[$pidKey])) {
            $qty += $this->_checkedProductsQty[$pidKey];
        }
        $this->_checkedProductsQty[$pidKey] = $qty;
        return $qty;
    }
    protected function _getQuoteItemQtyForCheck($productId, $quoteItemId, $itemQty)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::_getQuoteItemQtyForCheck($productId, $quoteItemId, $itemQty);
        }
        $qty = $itemQty;
        $pidKey = $this->getUdropshipVendor() ? $this->getUdropshipVendor().'-'.$productId : $productId;
        if (isset($this->_checkedQuoteItems[$pidKey]['qty']) &&
            !in_array($quoteItemId, $this->_checkedQuoteItems[$pidKey]['items'])) {
                $qty += $this->_checkedQuoteItems[$pidKey]['qty'];
        }

        $this->_checkedQuoteItems[$pidKey]['qty'] = $qty;
        $this->_checkedQuoteItems[$pidKey]['items'][] = $quoteItemId;

        return $qty;
    }
    public function checkQuoteItemQty($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::checkQuoteItemQty($observer);
        }
        /*
        $this->_udmultiQuoteItem = $observer->getEvent()->getItem();
        $result = parent::checkQuoteItemQty($observer);
        $this->_udmultiQuoteItem = null;
        */
        return $this;
    }

    public function subtractQuoteInventory(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::subtractQuoteInventory($observer);
        }
        $quote = $observer->getEvent()->getQuote();

        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return;
        }
        $update = array();
        $allPids = array();
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getChildren()) {
                $pId = $item->getProductId();
                $vId = $item->getUdropshipVendor();
                $v = Mage::helper('udropship')->getVendor($vId);
                if (!$v->getId() && $v->getStockcheckMethod()) continue;
                $allPids[$pId] = $pId;
                if (isset($update[$vId][$pId])) {
                    $update[$vId][$pId]['stock_qty_add'] -= $item->getTotalQty();
                } else {
                    $update[$vId][$pId] = array(
                        'stock_qty_add' => -$item->getTotalQty(),
                    );
                }
            }
        }

        if (empty($allPids)) {
            return $this;
        }

        $siData = Mage::getResourceSingleton('udropship/helper')->loadDbColumnsForUpdate(
            Mage::getModel('cataloginventory/stock_item'),
            array('product_id'=>$allPids),
            array('backorders','use_config_backorders')
        );

        $hlpm = Mage::helper('udmulti');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getReadConnection();
        foreach ($update as $vId=>$_update) {
            $mvData = $rHlp->loadDbColumnsForUpdate(
                Mage::getModel('udropship/vendor_product'),
                array('product_id'=>array_keys($_update)),
                array('backorders','stock_qty','product_id','avail_state','avail_date','status'),
                $conn->quoteInto('{{table}}.vendor_id=?', $vId)
            );
            foreach ($_update as $pId => $_prod) {
                $qtyCheck = abs($_prod['stock_qty_add']);
                if (!array_key_exists($pId, $mvData)) {
                    if (Mage::app()->getStore()->isAdmin()) continue;
                    Mage::throwException(
                        Mage::helper('udropship')->__('Stock configuration problem')
                    );
                }
                $_mv = $mvData[$pId];
                if (!$hlpm->isQtySalableByVendorData($qtyCheck, (array)@$siData[$pId], $vId, $_mv)) {
                    if (Mage::app()->getStore()->isAdmin()) continue;
                    Mage::throwException(
                        Mage::helper('udropship')->__('Not all products are available in the requested quantity')
                    );
                }
            }

        }
        foreach ($update as $vId=>$_update) {
            Mage::helper('udmulti')->setReindexFlag(false);
            Mage::helper('udmulti')->saveThisVendorProductsPidKeys($_update, $vId);
            Mage::helper('udmulti')->setReindexFlag(true);
        }

        $quote->setInventoryProcessed(true);
        return $this;
    }

    public function revertQuoteInventory($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::revertQuoteInventory($observer);
        }
        $quote = $observer->getEvent()->getQuote();

        if (!$quote->getInventoryProcessed()) {
            return;
        }
        $update = array();
        foreach ($quote->getAllItems() as $item) {
            if (!$item->getChildren()) {
                $pId = $item->getProductId();
                $vId = $item->getUdropshipVendor();
                if (isset($update[$vId][$pId])) {
                    $update[$vId][$pId]['stock_qty_add'] += $item->getTotalQty();
                } else {
                    $update[$vId][$pId] = array(
                        'stock_qty_add' => $item->getTotalQty(),
                    );
                }
            }
        }

        foreach ($update as $vId=>$_update) {
            Mage::helper('udmulti')->setReindexFlag(false);
            Mage::helper('udmulti')->saveThisVendorProductsPidKeys($_update, $vId);
            Mage::helper('udmulti')->setReindexFlag(true);
        }

        $quote->setInventoryProcessed(false);
    }

    public function cancelOrderItem($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::cancelOrderItem($observer);
        }
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

        if ($item->getId() && ($productId = $item->getProductId()) && empty($children)) {
            $qty = $item->getQtyOrdered() - $item->getQtyCanceled();
            $parentItem = $item->getParentItem();
            $qtyInvoiced = $qtyShipped = 0;
            if ($item->isDummy(true) && $parentItem) {
                $parentQtyShipped = $parentItem->getQtyShipped();
                $parentQtyOrdered = $parentItem->getQtyOrdered();
                $parentQtyOrdered = $parentQtyOrdered > 0 ? $parentQtyOrdered : 1;
                $qtyShipped = $parentQtyShipped*$item->getQtyOrdered()/$parentQtyOrdered;
            } elseif (!$item->isDummy(true)) {
                $qtyShipped = $item->getQtyShipped();
            }
            if ($item->isDummy() && $parentItem) {
                $parentQtyInvoiced = $parentItem->getQtyInvoiced();
                $parentQtyOrdered = $parentItem->getQtyOrdered();
                $parentQtyOrdered = $parentQtyOrdered > 0 ? $parentQtyOrdered : 1;
                $qtyInvoiced = $parentQtyInvoiced*$item->getQtyOrdered()/$parentQtyOrdered;
            } elseif (!$item->isDummy()) {
                $qtyInvoiced = $item->getQtyInvoiced();
            }
            $qty -= max($qtyShipped, $qtyInvoiced);
            if ($qty>0) {
                Mage::helper('udmulti')->saveThisVendorProductsPidKeys(
                    array($productId=>array('stock_qty_add'=>$qty)),
                    $item->getUdropshipVendor()
                );
            }
        }

        return $this;
    }

    public function refundOrderInventory($observer)
    {
        if (!Mage::helper('udmulti')->isActive()) {
            return parent::refundOrderInventory($observer);
        }
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $parentItems = $items = array();
        
        foreach ($creditmemo->getAllItems() as $item) {
            $return = false;
            if ($item->hasBackToStock()) {
                if ($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
                $return = true;
            }
            $oItem = $item->getOrderItem();
            $children = $oItem->getChildrenItems() ? $oItem->getChildrenItems() : $oItem->getChildren();
            if (($oParent = $oItem->getParentItem())) {
                $parentItem = @$parentItems[$oParent->getId()];
            } else {
                $parentItem = null;
            }
            if ($children) {
                $parentItems[$oItem->getId()] = $item;
            } elseif ($return && ($vId = $oItem->getUdropshipVendor())) {
                $qty = null;
                if ($oItem->isDummy() && $parentItem) {
                    $parentQtyOrdered = $parentItem->getOrderItem()->getQtyOrdered();
                    $parentQtyOrdered = $parentQtyOrdered > 0 ? $parentQtyOrdered : 1;
                    $qty = $parentItem->getQty()*$oItem->getQtyOrdered()/$parentQtyOrdered;
                } elseif (!$oItem->isDummy()) {
                    $qty = $item->getQty();;
                }
                if ($qty !== null) {
                    if (isset($items[$vId][$item->getProductId()])) {
                        $items[$vId][$item->getProductId()]['stock_qty_add'] += $qty;
                    } else {
                        $items[$vId][$item->getProductId()] = array(
                            'stock_qty_add' => $qty,
                        );
                    }
                }
            }
        }
        if (!empty($items)) {
            $reindexPids = array();
            foreach ($items as $vId=>$update) {
                $reindexPids = array_merge($reindexPids, array_keys($update));
                Mage::helper('udmulti')->setReindexFlag(false);
                Mage::helper('udmulti')->saveThisVendorProductsPidKeys($update, $vId);
                Mage::helper('udmulti')->setReindexFlag(true);
            }
            $reindexPids = array_unique($reindexPids);
            $indexer = Mage::getSingleton('index/indexer');
            $pAction = Mage::getModel('catalog/product_action');
            $idxEvent = Mage::getModel('index/event')
                ->setEntity(Mage_Catalog_Model_Product::ENTITY)
                ->setType(Mage_Index_Model_Event::TYPE_MASS_ACTION)
                ->setDataObject($pAction);
            /* hook to cheat index process to be executed */
            $pAction->setWebsiteIds(array(0));
            $pAction->setProductIds($reindexPids);
            $indexer->getProcessByCode('cataloginventory_stock')->register($idxEvent)->processEvent($idxEvent);
            $indexer->getProcessByCode('catalog_product_price')->register($idxEvent)->processEvent($idxEvent);
            $indexer->getProcessByCode('udropship_vendor_product_assoc')->register($idxEvent)->processEvent($idxEvent);
        }
        //Mage::getSingleton('cataloginventory/stock')->revertProductsSale($items);
    }
}