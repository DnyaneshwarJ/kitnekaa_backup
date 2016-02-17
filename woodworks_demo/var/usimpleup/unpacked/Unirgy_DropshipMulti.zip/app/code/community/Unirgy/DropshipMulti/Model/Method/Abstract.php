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
 * @package    Unirgy_DropshipMulti
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMulti_Model_Method_Abstract extends Varien_Object
{
    public function collectStockLevels($items)
    {
        $hlp = Mage::helper('udropship');
        $outOfStock = array();
        $qtys = array();
        $stockItems = array();
        $skus = array();
        $costs = array();
        $itemsByPid = array();
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
                $qtysBySku[$skus[$pId]] = 0;
                $itemsByPid[$pId] = $item;
            }
            $qtys[$pId] += $hlp->getItemStockCheckQty($item);
            $qtysBySku[$skus[$pId]] = $qtys[$pId];
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
                $data['address_match'] = $v->isAddressMatch($hlp->getAddressByItem($itemsByPid[$pId]));
                $data['zipcode_match'] = $v->isZipcodeMatch($hlp->getZipcodeByItem($itemsByPid[$pId]));
                $data['country_match'] = $v->isCountryMatch($hlp->getCountryByItem($itemsByPid[$pId]));
                $requests[$method]['products'][$pId]['vendors'][$vId] = $data;
            }
        }

        $availability = Mage::getSingleton('udropship/stock_availability');
        $result = $availability->processRequests($items, $requests);
        $this->setStockResult($result);

        return $this;
    }

    public function checkLocalStockLevel($products)
    {
        $this->setTrueStock(true);
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $result = array();
        $ignoreStockStatusCheck = Mage::registry('reassignSkipStockCheck');
        $ignoreAddrCheck = Mage::registry('reassignSkipAddrCheck');
        foreach ($products as $pId=>$p) {
            foreach ($p['vendors'] as $vId=>$v) {
                $vQty = $v['stock_qty'];
                if ($vId==$localVendorId && is_null($vQty)) {
                    if (empty($p['stock_item'])) {
                        $p['stock_item'] = Mage::getModel('catalog/product')->load($pId)->getStockItem();
                    }
                    $v['stock_status'] = $ignoreStockStatusCheck
                        || !$p['stock_item']->getManageStock()
                        || $p['stock_item']->getIsInStock() && $p['stock_item']->checkQty($p['qty_requested']);
                } else {
                    $v['stock_status'] = $ignoreStockStatusCheck || is_null($vQty) || $vQty>=$p['qty_requested'];
                }
                $zipCodeMatch = (!isset($v['zipcode_match']) || $v['zipcode_match']!==false);
                $countryMatch = (!isset($v['country_match']) || $v['country_match']!==false);
                $v['addr_status'] = $zipCodeMatch && $countryMatch;
                if ($ignoreAddrCheck) {
                    $v['addr_status'] = true;
                }
                $v['status'] = $v['stock_status'] && $v['addr_status'];
                $v['zipcode_match'] = (!isset($v['zipcode_match']) || $v['zipcode_match']!==false);
                $v['country_match'] = (!isset($v['country_match']) || $v['country_match']!==false);
                $result[$pId][$vId] = $v;
            }
        }
        $this->setTrueStock(false);
        return $result;
    }
}
