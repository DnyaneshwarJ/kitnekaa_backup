<?php

class Unirgy_DropshipTierShipping_Model_Carrier
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

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if ($this->getConfigFlag('use_simple_rates')) {
            return $this->_collectSimpleRates($request);
        } else {
            return $this->_collectRates($request);
        }
    }

    protected function _collectSimpleRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $items = $request->getAllItems();
        $hlpd = Mage::helper('udropship/protected');
        $tsHlp = Mage::helper('udtiership');
        $quote = Mage::helper('udropship/item')->getQuote($items);
        $address = Mage::helper('udropship/item')->getAddress($items);
        $cscId = 0;
        if ($hasShipClass = Mage::helper('udropship')->isModuleActive('udshipclass')) {
            $cscId = Mage::helper('udshipclass')->getCustomerShipClass($address);
        }

        $vId = $request->getVendorId();
        $store = $quote->getStore();
        $locale = Mage::app()->getLocale();
        $tierRates = $vId ? $this->getTiershipSimpleRates($vId) : array();
        $vendor = $vId ? Mage::helper('udropship')->getVendor($vId) : new Varien_Object();
        $globalTierRates = $this->getGlobalTierShipConfigSimple();
        $costRate = !$this->_isRateEmpty(@$tierRates[$cscId]['cost'])
            ? @$tierRates[$cscId]['cost'] : @$globalTierRates[$cscId]['cost'];
        $additionalRate = !$this->_isRateEmpty(@$tierRates[$cscId]['additional'])
            ? @$tierRates[$cscId]['additional'] : @$globalTierRates[$cscId]['additional'];
        if ($this->_isRateEmpty($costRate)) {
            $costRate = $tsHlp->getFallbackRateValue('cost', $store);
        }
        if ($this->_isRateEmpty($additionalRate)) {
            $additionalRate = $tsHlp->getFallbackRateValue('additional', $store);
        }
        $total = 0;
        $costUsed = false;
        $costUsedByPid = array();
        $costProdAttr = $tsHlp->getProductAttribute('cost', $this->getStore());
        $additionalProdAttr = $tsHlp->getProductAttribute('additional', $this->getStore());
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) continue;
            $product = $item->getProduct();
            $pId = $product->getId();
            $_qty = $this->getItemCalculationQty($item);
            $pCost = $pAdditional = null;
            if ($costProdAttr) $pCost = $product->getData($costProdAttr);
            if ($additionalProdAttr) $pAdditional = $product->getData($additionalProdAttr);
            if (!$this->_isRateEmpty($pCost)) {
                if ($_qty>0 && empty($costUsedByPid[$pId])) {
                    $costUsedByPid[$pId] = true;
                    $total += $pCost;
                    $_qty--;
                }
            } elseif (!$costUsed) {
                if ($_qty>0) {
                    $costUsed = true;
                    $total += $costRate;
                    $_qty--;
                }
            }
            if (!$this->_isRateEmpty($pAdditional)) {
                $total += $pAdditional*$_qty;
            } else {
                $total += $additionalRate*$_qty;
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('total');
        $method->setMethodTitle($this->getConfigData('name'));

        $price = $this->getFinalPriceWithHandlingFee($total);

        $method->setPrice($price);
        $method->setCost($total);

        $result->append($method);

        return $result;
    }

    protected function _isRateEmpty($value)
    {
        return null===$value||false===$value||''===$value;
    }

    protected function _collectRates(Mage_Shipping_Model_Rate_Request $request)
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
            $vscId = Mage::helper('udshipclass')->getVendorShipClass($request->getVendorId());
            $cscId = Mage::helper('udshipclass')->getCustomerShipClass($address);
        }

        $vId = $request->getVendorId();
        $store = $quote->getStore();
        $locale = Mage::app()->getLocale();
        $tierRates = $vId ? $this->getTiershipRates($vId) : array();
        $vendor = $vId ? Mage::helper('udropship')->getVendor($vId) : new Varien_Object();
        $globalTierRates = $this->getGlobalTierShipConfig();
        $rateReq = new Unirgy_DropshipTierShipping_Model_RateReq(array(
            'data_object' => new Varien_Object($tierRates),
            'global_data_object' => new Varien_Object($globalTierRates),
            'store' => $store,
            'vendor' => $vendor,
            'locale' => $locale
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
                        $rateReq->initKey($catId, $vscId, $cscId);
                        $rateReq->setSubkeys(array('cost', 'additional', 'handling'));
                        $_rateToUse = $rateReq->getResult();
                    }
                    $exactMatched = $exactMatched || $_exactMatched;
                    $subcatMatched = $subcatMatched || $_subcatMatched;
                }
            }
            if ($_rateToUse) {
                $_rateToUse->setData('item_qty', $this->getItemCalculationQty($item));
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
                            $_handling = $__total*$rateToUse->getHandling()/100;
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

        $method->setMethod('total');
        $method->setMethodTitle($this->getConfigData('name'));

        $price = $this->getFinalPriceWithHandlingFee($total);

        $method->setPrice($price);
        $method->setCost($total);

        $result->append($method);

        return $result;
    }

    protected function _getRateToUse($tierRates, $globalTierRates, $catId, $vscId, $cscId, $field)
    {
        return Mage::helper('udtiership')->getRateToUse($tierRates, $globalTierRates, $catId, $vscId, $cscId, $field);
    }

    public function getTiershipRates($vendor)
    {
        return Mage::helper('udtiership')->getVendorTiershipRates($vendor);
    }

    public function getGlobalTierShipConfig()
    {
        return Mage::helper('udtiership')->getGlobalTierShipConfig();
    }

    public function getTiershipSimpleRates($vendor)
    {
        return Mage::helper('udtiership')->getVendorTiershipSimpleRates($vendor);
    }

    public function getGlobalTierShipConfigSimple()
    {
        return Mage::helper('udtiership')->getGlobalTierShipConfigSimple();
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('total'=>$this->getConfigData('name'));
    }
}