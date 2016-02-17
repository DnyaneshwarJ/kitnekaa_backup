<?php

class Unirgy_DropshipMulti_Model_Stock_Availability
    extends Unirgy_Dropship_Model_Stock_Availability
{

    public function collectStockLevels($items, $options=array())
    {
        $hlp = Mage::helper('udropship');
        $iHlp = Mage::helper('udropship/item');
        $outOfStock = array();
        $extraQtys = array();
        $qtys = array();
        $stockItems = array();
        $skus = array();
        $costs = array();
        $zipCodes = array();
        $countries = array();
        $perItemData = array();
        foreach ($items as $item) {
            if (empty($quote)) {
                $quote = $item->getQuote();
            }
            //if ($iHlp->isVirtual($item)) continue;
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
                $zipCodes[$pId] = $hlp->getZipcodeByItem($item);
                $countries[$pId] = $hlp->getCountryByItem($item);
                $addresses[$pId] = $hlp->getAddressByItem($item);
            }
            $qtys[$pId] += $hlp->getItemStockCheckQty($item);
            $extraQtys[$pId] = $item->getUdropshipExtraStockQty();
            if (empty($perItemData[$pId])) {
                $perItemData[$pId] = array();
            }
            $perItemData[$pId][spl_object_hash($item)] = array(
                'parent_item_id' => $item->getParentItemId(),
                'item_id' => $item->getId(),
                'qty_requested' => $hlp->getItemStockCheckQty($item),
                'forced_vendor_id' => $iHlp->getForcedVendorIdOption($item),
                'priority_vendor_id' => $iHlp->getPriorityVendorIdOption($item),
                'skip_stock_check' => $iHlp->getSkipStockCheckVendorOption($item),
                'vendors' => array()
            );
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
        foreach ($perItemData as $pId=>&$_itemData) {
            uasort($_itemData, array($this, 'sortPerItemData'));
        }
        unset($_itemData);
        $vendorData = Mage::helper('udmulti')->getActiveMultiVendorData($items);

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
                        'per_item_data' => $perItemData[$pId],
                        'vendors' => array(),
                    );
                }
                $data = $vp->getData();
                $data['__qty_used'] = 0;
                $data['stock_qty'] = is_null($vp->getStockQty()) || $vp->getStockQty()==='' ? null : 1*$vp->getStockQty()+@$extraQtys[$pId][$vId];
                $data['vendor_sku'] = $vp->getVendorSku() ? $vp->getVendorSku() : $skus[$pId];
                $data['vendor_cost'] = $vp->getVendorCost() ? $vp->getVendorCost() : $costs[$pId];
                $data['address_match'] = $v->isAddressMatch($addresses[$pId]);
                $data['zipcode_match'] = $v->isZipcodeMatch($zipCodes[$pId]);
                $data['country_match'] = $v->isCountryMatch($countries[$pId]);
                $requests[$method]['products'][$pId]['vendors'][$vId] = $data;
            }
        }

        $iHlp->processSameVendorLimitation($items, $requests);

        $result = $this->processRequests($items, $requests);
        $this->setStockResult($result);

        return $this;
    }

    public function checkLocalStockLevel($products)
    {
        $this->setTrueStock(true);
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $result = array();
        $hlpm = Mage::helper('udmulti');
        $ignoreStockStatusCheck = Mage::registry('reassignSkipStockCheck');
        $ignoreAddrCheck = Mage::registry('reassignSkipAddrCheck');
        foreach ($products as $pId=>$p) {
            if (empty($p['stock_item'])) {
                $p['stock_item'] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($pId);
            }
            $qtyRequested = 0;
            $perItemData = array();
            foreach ($p['per_item_data'] as $itemHash => $itemData) {
                if (!array_key_exists('vendors', $itemData)) {
                    $itemData['vendors'] = array();
                }
                if (!array_key_exists('status', $itemData)) {
                    $itemData['status'] = false;
                }
                $iQtyRequested = $itemData['qty_requested'];
                $_forcedVid = @$itemData['forced_vendor_id'];
                if ($_forcedVid) {
                    $_mvd = (array)@$p['vendors'][$_forcedVid];
                    $_mvd['is_priority_vendor'] = false;
                    $_mvd['is_forced_vendor'] = true;
                    $_mvd['vendor_id'] = $_forcedVid;
                    $addressMatch = (!isset($_mvd['address_match']) || $_mvd['address_match']!==false);
                    $zipCodeMatch = (!isset($_mvd['zipcode_match']) || $_mvd['zipcode_match']!==false);
                    $countryMatch = (!isset($_mvd['country_match']) || $_mvd['country_match']!==false);
                    if (!empty($itemData['skip_stock_check'])) {
                        $_mvd['qty_in_stock'] = 0;
                        $_mvd['backorders'] = false;
                        $_mvd['stock_status'] = true;
                    } else {
                        if (empty($_mvd)) {
                            $_mvd['qty_in_stock'] = 0;
                            $_mvd['backorders'] = false;
                            $_mvd['stock_status'] = false;
                        } else {
                            $_mvd['qty_in_stock'] = $hlpm->getQtyFromMvData($_mvd);
                            $_mvd['backorders'] = $hlpm->getBackorders(array($_forcedVid=>$_mvd), $p['stock_item']);
                            $_mvd['stock_status'] = $hlpm->isQtySalableByVendorData($iQtyRequested, $p['stock_item'], $_forcedVid, $_mvd);
                            $p['vendors'][$_forcedVid]['__qty_used'] = @$p['vendors'][$_forcedVid]['__qty_used'] + $iQtyRequested;
                        }
                    }
                    if ($ignoreStockStatusCheck) {
                        $_mvd['stock_status'] = true;
                    }
                    $_mvd['addr_status'] = $zipCodeMatch && $countryMatch && $addressMatch;
                    if ($ignoreAddrCheck) {
                        $_mvd['addr_status'] = true;
                    }
                    $_mvd['status'] = $_mvd['stock_status'] && $_mvd['addr_status'];
                    $_mvd['address_match'] = $addressMatch;
                    $_mvd['zipcode_match'] = $zipCodeMatch;
                    $_mvd['country_match'] = $countryMatch;
                    $itemData['vendors'][$_forcedVid] = $_mvd;
                } else {
                    foreach ($p['vendors'] as $vId=>$v) {
                        $_mvd = $v;
                        $_mvd['is_priority_vendor'] = $itemData['priority_vendor_id']==$vId;
                        $_mvd['is_forced_vendor'] = false;
                        $_mvd['vendor_id'] = $vId;
                        $addressMatch = (!isset($_mvd['address_match']) || $_mvd['address_match']!==false);
                        $zipCodeMatch = (!isset($_mvd['zipcode_match']) || $_mvd['zipcode_match']!==false);
                        $countryMatch = (!isset($_mvd['country_match']) || $_mvd['country_match']!==false);
                        if (!empty($itemData['skip_stock_check'])) {
                            $_mvd['qty_in_stock'] = 0;
                            $_mvd['backorders'] = false;
                            $_mvd['stock_status'] = true;
                        } else {
                            if (empty($_mvd)) {
                                $_mvd['qty_in_stock'] = 0;
                                $_mvd['backorders'] = false;
                                $_mvd['stock_status'] = false;
                            } else {
                                $_mvd['qty_in_stock'] = $hlpm->getQtyFromMvData($_mvd);
                                $_mvd['backorders'] = $hlpm->getBackorders(array($vId=>$_mvd), $p['stock_item']);
                                $_mvd['stock_status'] = $hlpm->isQtySalableByVendorData($iQtyRequested, $p['stock_item'], $vId, $_mvd);
                                $p['vendors'][$vId]['__qty_used'] = @$p['vendors'][$vId]['__qty_used'] + $iQtyRequested;
                            }
                        }
                        if ($ignoreStockStatusCheck) {
                            $_mvd['stock_status'] = true;
                        }
                        $_mvd['addr_status'] = $zipCodeMatch && $countryMatch && $addressMatch;
                        if ($ignoreAddrCheck) {
                            $_mvd['addr_status'] = true;
                        }
                        $_mvd['status'] = $_mvd['stock_status'] && $_mvd['addr_status'];
                        $_mvd['address_match'] = $addressMatch;
                        $_mvd['zipcode_match'] = $zipCodeMatch;
                        $_mvd['country_match'] = $countryMatch;
                        $itemData['vendors'][$vId] = $_mvd;
                    }
                    $qtyRequested += $iQtyRequested;
                }
                $perItemData[$itemHash] = $itemData;
            }
            unset($itemData);
            foreach ($p['vendors'] as $vId=>$v) {
                unset($v['__qty_used']);
                $v['qty_in_stock'] = $hlpm->getQtyFromMvData($v);
                $v['backorders'] = $hlpm->getBackorders(array($vId=>$v), $p['stock_item']);
                $v['stock_status'] = true;

                $v['per_item_data'] = array();
                foreach ($perItemData as $itemHash => $itemData) {
                    foreach ($itemData['vendors'] as $_mvdVid => $_mvd) {
                        if ($_mvdVid == $vId) {
                            $v['per_item_data'][$itemHash] = $_mvd;
                            $v['stock_status'] = $v['stock_status'] && $_mvd['stock_status'];
                            break;
                        }
                    }
                }

                $v['global_stock_status'] = $hlpm->isQtySalableByVendorData($qtyRequested, $p['stock_item'], $vId, $v);

                if ($ignoreStockStatusCheck) $v['stock_status'] = true;

                $addressMatch = (!isset($v['address_match']) || $v['address_match']!==false);
                $zipCodeMatch = (!isset($v['zipcode_match']) || $v['zipcode_match']!==false);
                $countryMatch = (!isset($v['country_match']) || $v['country_match']!==false);
                $v['addr_status'] = $zipCodeMatch && $countryMatch && $addressMatch;
                if ($ignoreAddrCheck) {
                    $v['addr_status'] = true;
                }
                $v['status'] = $v['stock_status'] && $v['addr_status'];
                $v['address_match'] = $addressMatch;
                $v['zipcode_match'] = $zipCodeMatch;
                $v['country_match'] = $countryMatch;
                $result[$pId][$vId] = $v;
            }
        }
        unset($p);
        $this->setTrueStock(false);
        return $result;
    }
    public function sortPerItemData($c1, $c2)
    {
        if ((bool)$c1['forced_vendor_id']>(bool)$c2['forced_vendor_id']) {
            return -1;
        } elseif ((bool)$c1['forced_vendor_id']<(bool)$c2['forced_vendor_id']) {
            return 1;
        }
        if ((bool)$c1['item_id']>(bool)$c2['item_id']) {
            return -1;
        } elseif ((bool)$c1['item_id']<(bool)$c2['item_id']) {
            return 1;
        }
        return $c1['item_id']>$c2['item_id'];
    }
}
