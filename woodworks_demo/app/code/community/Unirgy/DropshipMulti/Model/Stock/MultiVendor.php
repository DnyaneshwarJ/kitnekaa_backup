<?php

class Unirgy_DropshipMulti_Model_Stock_Availability
    extends Unirgy_Dropship_Model_Stock_Availability
{
    public function collectStockLevels($items)
    {
        $hlp = Mage::helper('udropship');
        $outOfStock = array();
        $qtys = array();
        $stockItems = array();
        $skus = array();
        $costs = array();
        foreach ($items as $item) {
            if (empty($quote)) {
                $quote = $item->getQuote();
            }
            if ($item->getHasChildren()) {
                continue;
            }

            $pId = $item->getProductId();
            if (empty($qtys[$pId])) {
                $qtys[$pId] = 0;
                $product = $item->getProduct();
                if (!$product) {
                    $product = Mage::getModel('catalog/product')->load($pId);
                }
                $stockItems[$pId] = $product->getStockItem();
                $skus[$pId] = $product->getSku();
                $costs[$pId] = $item->getCost();
            }
            $parentQty = $item->getParentItem() ? $item->getParentItem()->getQty() : 1;
            $qtys[$pId] += $item->getQty()*$parentQty;
            /*
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $product = $child->getProduct();
                    if ($product->getTypeInstance()->isVirtual()) {
                        continue;
                    }
                    $pId = $child->getProductId();
                    if (empty($qtys[$pId])) {
                        $qtys[$pId] = 0;
                        $stockItems[$pId] = $child->getProduct()->getStockItem();
                    }
                    $qtys[$pId] += $item->getQty()*$child->getQty();
                }
            }
            */
        }
        $vendorData = Mage::helper('udmulti')->getMultiVendorData($items);

        $requests = array();
        foreach ($qtys as $pId=>$qty) {
            foreach ($vendorData as $vp) {
                if ($vp->getProductId()!=$pId) {
                    continue;
                }
                $vId = $vp->getVendorId();
                $v = $hlp->getVendor($vId);
                $method = $v->getStockcheckMethod() ? $v->getStockcheckMethod() : 'local_multi';
                $cb = $v->getStockcheckCallback($method);

                if (empty($requests[$method])) {
                    $requests[$method] = array(
                        'callback' => $cb,
                        'products' => array(),
                    );
                }
                if (empty($requests[$method]['products'][$pId])) {
                    $requests[$method]['products'][$pId] = array(
                        'stock_item' => $stockItems[$pId],
                        'qty_requested' => $qty,
                        'vendors' => array(),
                    );
                }
                $data = $vp->getData();
                $data['stock_qty'] = is_null($vp->getStockQty()) || $vp->getStockQty()==='' ? null : 1*$vp->getStockQty();
                $data['vendor_sku'] = $vp->getVendorSku() ? $vp->getVendorSku() : $skus[$pId];
                $data['vendor_cost'] = $vp->getVendorCost() ? $vp->getVendorCost() : $costs[$pId];
                $requests[$method]['products'][$pId]['vendors'][$vId] = $data;
            }
        }

        $result = $this->processRequests($items, $requests);
        $this->setStockResult($result);

        return $this;
    }

    public function checkLocalStockLevel($products)
    {
        $this->setTrueStock(true);
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $result = array();
        foreach ($products as $pId=>$p) {
            foreach ($p['vendors'] as $vId=>$v) {
                $vQty = $v['stock_qty'];
                if ($vId==$localVendorId && is_null($vQty)) {
                    if (empty($p['stock_item'])) {
                        $p['stock_item'] = Mage::getModel('catalog/product')->load($pId)->getStockItem();
                    }
                    $v['status'] = !$p['stock_item']->getManageStock()
                        || $p['stock_item']->getIsInStock() && $p['stock_item']->checkQty($p['qty_requested']);
                } else {
                    $v['status'] = is_null($vQty) || $vQty>=$p['qty_requested'];
                }
                $result[$pId][$vId] = $v;
            }
        }
        $this->setTrueStock(false);
        return $result;
    }
}