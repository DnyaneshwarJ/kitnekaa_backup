<?php

class Unirgy_DropshipTierShipping_Model_V2_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'udtiership';

    public function getItemCalculationQty($item)
    {
        $qty = $item->getTotalQty();
        $address = Mage::helper('udropship/item')->getAddress($item);
        if ($item->getFreeShipping() === true
            || $address instanceof Varien_Object && $address->getFreeShipping() === true
        ) {
            $qty = 0;
        } elseif ($item->getFreeShipping()) {
            $qty = max(0,$qty-$item->getFreeShipping());
        }
        return $qty;
    }
    public function getItemCalculationWeight($item)
    {
        $qty = $this->getItemCalculationQty($item);
        return $qty ? $item->getFullRowWeight()/$qty : $qty;
    }
    public function getItemCalculationPrice($item)
    {
        $qty = $this->getItemCalculationQty($item);
        return $qty ? $item->getBaseRowTotal()/$qty : $qty;
    }

    protected $_quote;
    protected $_address;
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $items = $request->getAllItems();
        $this->_quote = Mage::helper('udropship/item')->getQuote($items);
        $this->_address = Mage::helper('udropship/item')->getAddress($items);
        $result = Mage::getModel('shipping/rate_result');
        $deliveryTypes = Mage::getResourceModel('udtiership/deliveryType_collection')->setDeliverySort()->toOptionHash();
        foreach ($deliveryTypes as $deliveryType=>$deliveryTypeLabel) {
            if (Mage::helper('udtiership')->isV2SimpleRates()) {
                $method = $this->_getSimpleRate($request, $deliveryType);
            } elseif (Mage::helper('udtiership')->isV2SimpleConditionalRates()) {
                $method = $this->_getSimpleCondRate($request, $deliveryType);
            } else {
                $method = $this->_getRate($request, $deliveryType);
            }
            if ($method) {
                $result->append($method);
            }
        }
        $this->_quote = null;
        $this->_address = null;
        return $result;
    }

    protected function _getSimpleRate(Mage_Shipping_Model_Rate_Request $request, $deliveryType)
    {
        $items = $request->getAllItems();
        $hlpd = Mage::helper('udropship/protected');
        $tsHlp = Mage::helper('udtiership');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();

        $quote = Mage::helper('udropship/item')->getQuote($items);
        $address = Mage::helper('udropship/item')->getAddress($items);

        $cscId = 0;
        $extraCond = array();
        if ($hasShipClass = Mage::helper('udropship')->isModuleActive('udshipclass')) {
            $cscId = (array)Mage::helper('udshipclass')->getAllCustomerShipClass($address);
            $cscCond = array();
            foreach ($cscId as $_cscId) {
                $cscCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_shipclass_id)',$_cscId);
            }
            $cgIds = array($quote->getCustomerGroupId(),'*');
            $cgCond = array();
            foreach ($cgIds as $_cgId) {
                $cgCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_group_id)',$_cgId);
            }
            if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                $extraCond = array(
                    '( '.implode(' OR ', $cscCond).' ) AND ('.implode(' OR ', $cgCond).' ) '
                );
            } else {
                $extraCond = array(
                    '( '.implode(' OR ', $cscCond).' ) '
                );
            }
            $extraCond['__order'] = array(
                Mage::helper('udropship/catalog')->getCaseSql('customer_shipclass_id', array_flip($cscCond), 9999),
            );
            if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                $extraCond['__order'][] = Mage::helper('udropship/catalog')->getCaseSql('customer_group_id', array_flip($cgCond), 9999);
            }
        }

        $vId = $request->getVendorId();
        $vendor = $vId ? Mage::helper('udropship')->getVendor($vId) : new Varien_Object();
        $store = $quote->getStore();
        $locale = Mage::app()->getLocale();

        $tierRates = array();
        if ($vendor && $vendor->getData('tiership_use_v2_rates')) {
            $tierRates = $tsHlp->getVendorV2SimpleRates($vendor->getId(), $deliveryType, $extraCond);
        } else {
            $tierRates = $tsHlp->getV2SimpleRates($deliveryType, $extraCond);
        }
        $hasTierRate = true;
        $costRate = '';
        $additionalRate = '';
        if (empty($tierRates)) {
            $hasTierRate = false;
            $tierRate = array();
        } else {
            reset($tierRates);
            $tierRate = current($tierRates);
            $costRate = $tierRate['cost'];
            $additionalRate = $tierRate['additional'];
        }
        $total = 0;
        $costUsed = false;
        $costUsedByPid = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) continue;
            $product = $item->getProduct();
            $pId = $product->getId();
            $_qty = $this->getItemCalculationQty($item);
            $pCost = $this->getProductRate($product, 'cost', $cscId, $deliveryType);
            $pAdditional = $this->getProductRate($product, 'additional', $cscId, $deliveryType);
            if ($product->getUdtiershipUseCustom() && $this->_isRateEmpty($pCost)) {
                return false;
            } elseif ($product->getUdtiershipUseCustom() && !$this->_isRateEmpty($pCost)) {
                if ($_qty>0 && empty($costUsedByPid[$pId])) {
                    $costUsedByPid[$pId] = true;
                    $total += $locale->getNumber($pCost);
                    $_qty--;
                }
            } elseif (!$hasTierRate) {
                return false;
            } elseif (!$costUsed) {
                if ($_qty>0) {
                    $costUsed = true;
                    $total += $locale->getNumber($costRate);
                    $_qty--;
                }
            }
            if (!$this->_isRateEmpty($pAdditional)) {
                $total += $locale->getNumber($pAdditional)*$_qty;
            } else {
                $total += $locale->getNumber($additionalRate)*$_qty;
            }
        }

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $deliveryTypes = Mage::getResourceModel('udtiership/deliveryType_collection')->toOptionHash();

        $method->setMethod($deliveryType);
        $method->setMethodTitle($deliveryTypes[$deliveryType]);

        $price = $this->getFinalPriceWithHandlingFee($total);

        $method->setPrice($price);
        $method->setCost($total);

        return $method;
    }

    protected function _getSimpleCondRate(Mage_Shipping_Model_Rate_Request $request, $deliveryType)
    {
        $items = $request->getAllItems();
        $hlpd = Mage::helper('udropship/protected');
        $tsHlp = Mage::helper('udtiership');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();

        $quote = Mage::helper('udropship/item')->getQuote($items);
        $address = Mage::helper('udropship/item')->getAddress($items);

        $cscId = 0;
        $extraCond = array();
        if ($hasShipClass = Mage::helper('udropship')->isModuleActive('udshipclass')) {
            $cscId = (array)Mage::helper('udshipclass')->getAllCustomerShipClass($address);
            $cscCond = array();
            foreach ($cscId as $_cscId) {
                $cscCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_shipclass_id)',$_cscId);
            }
            $cgIds = array($quote->getCustomerGroupId(),'*');
            $cgCond = array();
            foreach ($cgIds as $_cgId) {
                $cgCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_group_id)',$_cgId);
            }
            if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                $extraCond = array(
                    '( '.implode(' OR ', $cscCond).' ) AND ('.implode(' OR ', $cgCond).' ) '
                );
            } else {
                $extraCond = array(
                    '( '.implode(' OR ', $cscCond).' ) '
                );
            }
            $extraCond['__order'] = array(
                Mage::helper('udropship/catalog')->getCaseSql('customer_shipclass_id', array_flip($cscCond), 9999),
            );
            if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                $extraCond['__order'][] = Mage::helper('udropship/catalog')->getCaseSql('customer_group_id', array_flip($cgCond), 9999);
            }
        }

        $vId = $request->getVendorId();
        $vendor = $vId ? Mage::helper('udropship')->getVendor($vId) : new Varien_Object();
        $store = $quote->getStore();
        $locale = Mage::app()->getLocale();

        $tierRates = array();
        if ($vendor && $vendor->getData('tiership_use_v2_rates')) {
            $tierRates = $tsHlp->getVendorV2SimpleCondRates($vendor->getId(), $deliveryType, $extraCond);
        } else {
            $tierRates = $tsHlp->getV2SimpleCondRates($deliveryType, $extraCond);
        }
        if (empty($tierRates)) {
            return false;
        }
        $totalQty = $totalWeight = $totalValue = 0;
        $total = 0;
        $costUsed = false;
        $costUsedByPid = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) continue;
            $product = $item->getProduct();
            $pId = $product->getId();
            $_qtyTotal = $item->getTotalQty();
            $_qty = $this->getItemCalculationQty($item);
            $pCost = $this->getProductRate($product, 'cost', $cscId, $deliveryType);
            if (!$this->_isRateEmpty($pCost)) {
                $total += $locale->getNumber($pCost)*$_qty;
            } else {
                $totalQty += $_qty;
                $totalWeight += ($_qty&&$_qtyTotal ? $item->getFullRowWeight()/$_qtyTotal*$_qty : 0);
                $totalValue += ($_qty&&$_qtyTotal ? $item->getBaseRowTotal()/$_qtyTotal*$_qty : 0);
            }
        }

        if ($totalQty!=0 || !$request->getAllItems()) {
            $tierRate = $this->_findCondRate($tierRates, array(
                Unirgy_DropshipTierShipping_Model_Source::SIMPLE_COND_FULLWEIGHT => $totalWeight,
                Unirgy_DropshipTierShipping_Model_Source::SIMPLE_COND_SUBTOTAL => $totalValue,
                Unirgy_DropshipTierShipping_Model_Source::SIMPLE_COND_TOTALQTY => $totalQty,
            ));
            if ($tierRate===false) {
                return false;
            }

            $total += $tierRate;
        }

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $deliveryTypes = Mage::getResourceModel('udtiership/deliveryType_collection')->toOptionHash();

        $method->setMethod($deliveryType);
        $method->setMethodTitle($deliveryTypes[$deliveryType]);

        $price = $this->getFinalPriceWithHandlingFee($total);

        $method->setPrice($price);
        $method->setCost($total);

        return $method;
    }
    protected function _findCondRate($tierRates, $conditions)
    {
        if (!is_array($tierRates)) return false;
        $result = false;
        foreach ($tierRates as $tierRate) {
            $curCondName = @$tierRate['condition_name'];
            if (empty($curCondName) || !array_key_exists($curCondName, $conditions)) continue;
            $condValue = $conditions[$curCondName];
            $curCond = @$tierRate['condition'];
            if (empty($curCond)) continue;
            if (!is_array($curCond)) {
                $curCond = Mage::helper('udropship')->unserialize($curCond);
            }
            if (!is_array($curCond)) continue;
            uasort($curCond, array($this, 'sortConditions'));
            foreach ($curCond as $cc) {
                if (!array_key_exists('condition_to', $cc) || !array_key_exists('price', $cc)) continue;
                if ($condValue<=$cc['condition_to']) {
                    $result = $cc['price'];
                    break 2;
                }
            }
        }
        return $result;
    }

    public function getProductRate($product, $sk, $cscId, $dt)
    {
        $tsHlp = Mage::helper('udtiership');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();
        $value = '';
        if ($product->getUdtiershipUseCustom()) {
            $value = false;
            if (!is_array($cscId)) {
                $cscId = array($cscId);
            }
            if (!is_array($dt)) {
                $dt = array($dt);
            }
            $extraCond = array();
            if ($hasShipClass = Mage::helper('udropship')->isModuleActive('udshipclass')) {
                $cscCond = array();
                foreach ($cscId as $_cscId) {
                    $cscCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_shipclass_id)',$_cscId);
                }
                $cgIds = array($this->_quote->getCustomerGroupId(),'*');
                $cgCond = array();
                foreach ($cgIds as $_cgId) {
                    $cgCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_group_id)',$_cgId);
                }
                if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                    $extraCond = array(
                        '( '.implode(' OR ', $cscCond).' ) AND ('.implode(' OR ', $cgCond).' ) '
                    );
                } else {
                    $extraCond = array(
                        '( '.implode(' OR ', $cscCond).' ) '
                    );
                }
                $extraCond['__order'] = array(
                    Mage::helper('udropship/catalog')->getCaseSql('customer_shipclass_id', array_flip($cscCond), 9999),
                );
                if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
                    $extraCond['__order'][] = Mage::helper('udropship/catalog')->getCaseSql('customer_group_id', array_flip($cgCond), 9999);
                }
            }

            $pRates = $tsHlp->getProductV2Rates($product, $dt, $extraCond);
            if (is_array($pRates)) {
                usort($pRates, array($this, 'sortBySortOrder'));
            } else {
                $pRates = array();
            }
            if (!empty($pRates)) {
                reset($pRates);
                $pRate = current($pRates);
                $value = Mage::app()->getLocale()->getNumber(@$pRate[$sk]);
            }
        }
        return $value;
    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }
    public function sortConditions($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        if (@$a['condition_to']<@$b['condition_to']) {
            return -1;
        } elseif (@$a['condition_to']>@$b['condition_to']) {
            return 1;
        }
        return 0;
    }

    protected function _isRateEmpty($value)
    {
        return null===$value||false===$value||''===$value;
    }

    protected function _getRate(Mage_Shipping_Model_Rate_Request $request, $deliveryType)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $items = $request->getAllItems();
        $hlpd = Mage::helper('udropship/protected');
        $tsHlp = Mage::helper('udtiership');
        $quote = Mage::helper('udropship/item')->getQuote($items);
        $address = Mage::helper('udropship/item')->getAddress($items);

        if ($hasShipClass = Mage::helper('udropship')->isModuleActive('udshipclass')) {
            $vscId = Mage::helper('udshipclass')->getAllVendorShipClass($request->getVendorId());
            $cscId = (array)Mage::helper('udshipclass')->getAllCustomerShipClass($address);
        }
        $cgIds = array($quote->getCustomerGroupId(),'*');

        $vId = $request->getVendorId();
        $store = $quote->getStore();
        $locale = Mage::app()->getLocale();
        $vendor = $vId ? Mage::helper('udropship')->getVendor($vId) : new Varien_Object();
        $globalTierRates = $this->getGlobalTierShipConfig();
        $rateReq = new Unirgy_DropshipTierShipping_Model_V2_RateReq(array(
            'store' => $store,
            'vendor' => $vendor,
            'locale' => $locale,
            'delivery_type' => $deliveryType
        ));
        $topCats = $tsHlp->getTopCategories();
        $catIdsToLoad = $catIds = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) continue;
            $product = $item->getProduct();
            $_catIds = $product->getCategoryIds();
            if (empty($_catIds)) continue;
            reset($_catIds);
            $catIdsToLoad = array_merge($catIdsToLoad, $_catIds);
            $catIds[$item->getId()] = $_catIds;
        }
        $catIdsToLoad = array_unique($catIdsToLoad);
        $iCats = Mage::getResourceModel('catalog/category_collection')->addIdFilter($catIdsToLoad);
        $subcatMatchFlag = Mage::getStoreConfigFlag('carriers/udtiership/match_subcategories');
        $ratesToUse = $ratesByHandling = $ratesByCost = array();
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) continue;
            $product = $item->getProduct();
            $pId = $product->getId();
            $rateReq->setProduct($product);
            $_rateToUse = false;
            if (!empty($ratesToUse[$pId])) {
                $ratesToUse[$pId]->setItemQty($ratesToUse[$pId]->getItemQty()+$this->getItemCalculationQty($item));
                $ratesToUse[$pId]->setTotalQty($ratesToUse[$pId]->getTotalQty()+$item->getTotalQty());
                continue;
            }
            if (!empty($catIds[$item->getId()])) {
                $exactMatched = $subcatMatched = false;
                foreach ($catIds[$item->getId()] as $iCatId) {
                    if (!($iCat = $iCats->getItemById($iCatId))) continue;
                    $_exactMatched = $_subcatMatched = false;
                    if ($topCats) $_exactMatched = $topCats->getItemById($iCatId);
                    $catId = null;
                    if ($_exactMatched) {
                        $catId = $iCatId;
                    } elseif ($subcatMatchFlag) {
                        $_catPath = explode(',', Mage::helper('udropship/catalog')->getPathInStore($iCat));
                        foreach ($_catPath as $_catPathId) {
                            if ($topCats && $topCats->getItemById($_catPathId)) {
                                $catId = $_catPathId;
                                $_subcatMatched = true;
                                break;
                            }
                        }
                    }
                    if ($catId && $topCats && $topCats->getItemById($catId)
                        && ($_exactMatched || !$exactMatched && !$_rateToUse)
                    ) {
                        $rateReq->init($catId, $vscId, $cscId, $cgIds);
                        $rateReq->setSubkeys(array('cost', 'additional', 'handling'));
                        $_rateToUse = $rateReq->getResult();
                    }
                    $exactMatched = $exactMatched || $_exactMatched;
                    $subcatMatched = $subcatMatched || $_subcatMatched;
                }
            }
            if ($_rateToUse===false) {
                $rateReq->init('*', $vscId, $cscId, $cgIds);
                $rateReq->setSubkeys(array('cost', 'additional', 'handling'));
                $_rateToUse = $rateReq->getResult();
            }
            if ($_rateToUse===false) {
                return false;
            }
            if ($_rateToUse) {
                $_rateToUse->setData('item_qty', $this->getItemCalculationQty($item));
                $_rateToUse->setData('total_qty', $item->getTotalQty());
                $ratesToUse[$pId] = $_rateToUse;
                $groupId = $_rateToUse->getCategoryId();
                if ($_rateToUse->isProductRate('cost')) {
                    $groupId = 'product'.$pId;
                } elseif ($_rateToUse->isFallbackRate('cost')) {
                    $groupId = 'fallback';
                }
                $ratesByCost[$groupId][] = $_rateToUse;
                $hGroupId = $_rateToUse->getCategoryId();
                if ($_rateToUse->isProductRate('handling')) {
                    $hGroupId = 'product'.$pId;
                } elseif ($_rateToUse->isFallbackRate('handling')) {
                    $hGroupId = 'fallback';
                }
                $ratesByHandling[$hGroupId][] = $_rateToUse;
                if (!isset($maxCost) || $maxCost<$_rateToUse->getData('cost')) {
                    $maxCost = $_rateToUse->getData('cost');
                    $maxCostGroupId = $groupId;
                    $maxCostId = $pId;
                }
                if (!isset($maxHandling) || $maxHandling<$_rateToUse->getData('handling')) {
                    $maxHandling = $_rateToUse->getData('handling');
                    $maxHandlingId = $pId;
                    $maxHandlingGroupId = $hGroupId;
                }
            }
        }

        $calculationMethod = $tsHlp->getCalculationMethod($store);

        $totalsByGroup = array();
        $total = 0;
        foreach ($ratesByCost as $groupId => $groupRates) {
            $_total = 0;
            foreach ($groupRates as $rateToUse) {
                $__total = 0;
                $qty = $rateToUse->getItemQty();
                if ($tsHlp->isMaxCalculationMethod($store)
                    && $rateToUse->getProductId()==$maxCostId
                    || $tsHlp->isSumCalculationMethod($store)
                ) {
                    if ($qty>0) {
                        $qty--;
                        $__total += $rateToUse->getCost();
                    }
                }
                if ($tsHlp->isMultiplyCalculationMethod($store)) {
                    $__total += $qty*$rateToUse->getCost();
                } elseif ($tsHlp->useAdditional($store)) {
                    $__total += $qty*$rateToUse->getAdditional();
                }
                $total += $__total;
                $_total += $__total;
            }
            $totalsByGroup[$groupId] = $_total;
        }

        $handling = 0;
        if ($tsHlp->useHandling($store)) {
            if ($tsHlp->useMaxFixedHandling($store)) {
                $handling = $maxHandling;
            } else {
                foreach ($ratesByHandling as $groupId => $groupRates) {
                    $_handling = 0;
                    foreach ($groupRates as $rateToUse) {
                        $__total = 0;
                        $qty = $rateToUse->getItemQty();
                        if ($tsHlp->isMaxCalculationMethod($store)
                            && $rateToUse->getProductId()==$maxCostId
                            || $tsHlp->isSumCalculationMethod($store)
                        ) {
                            if ($qty>0) {
                                $qty--;
                                $__total += $rateToUse->getCost();
                            }
                        }
                        if ($tsHlp->isMultiplyCalculationMethod($store)) {
                            $__total += $qty*$rateToUse->getCost();
                        } elseif ($tsHlp->useAdditional($store)) {
                            $__total += $qty*$rateToUse->getAdditional();
                        }
                        if ($tsHlp->usePercentHandling($store)) {
                            $_handling += $__total*$rateToUse->getHandling()/100;
                        } elseif ($tsHlp->useFixedHandling($store)) {
                            if ($rateToUse->getHandling()>$_handling) {
                                $_handling = $rateToUse->getHandling();
                            }
                        }
                    }
                    $handling += $_handling;
                }
            }
        }

        $total += $handling;

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $deliveryTypes = Mage::getResourceModel('udtiership/deliveryType_collection')->toOptionHash();

        $method->setMethod($deliveryType);
        $method->setMethodTitle($deliveryTypes[$deliveryType]);

        $price = $this->getFinalPriceWithHandlingFee($total);

        $method->setPrice($price);
        $method->setCost($total);

        $result->append($method);

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return Mage::getResourceModel('udtiership/deliveryType_collection')->toOptionHash();
    }
}