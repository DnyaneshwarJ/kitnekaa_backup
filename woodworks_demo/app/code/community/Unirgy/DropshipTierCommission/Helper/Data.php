<?php

class Unirgy_DropshipTierCommission_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getProductAttributeCode($key, $store=null)
    {
        $cfgKey = sprintf('udropship/tiercom/%s_attribute', $key);
        $cfgVal = Mage::getStoreConfig($cfgKey, $store);
        return $cfgVal;
    }
    public function getProductAttribute($key, $store=null)
    {
        if (($attrCode = $this->getProductAttributeCode($key, $store))) {
            return Mage::helper('udropship')->getProductAttribute($attrCode);
        }
        return false;
    }
    public function getCommProductAttribute($store=null)
    {
        return $this->getProductAttribute('comm', $store);
    }
    public function getFixedRateProductAttribute($store=null)
    {
        return $this->getProductAttribute('fixed_rate', $store);
    }
    public function processTiercomRates($vendor, $serialize=false)
    {
        $tiercomRates = $vendor->getData('tiercom_rates');
        if ($serialize) {
            if (is_array($tiercomRates)) {
                $tiercomRates = serialize($tiercomRates);
            }
        } else {
            if (is_string($tiercomRates)) {
                $tiercomRates = unserialize($tiercomRates);
            }
            if (!is_array($tiercomRates)) {
                $tiercomRates = array();
            }
        }
        $vendor->setData('tiercom_rates', $tiercomRates);
    }

    public function processTiercomFixedRates($vendor, $serialize=false)
    {
        $tiercomRates = $vendor->getData('tiercom_fixed_rates');
        if (is_string($tiercomRates)) {
            $tiercomRates = unserialize($tiercomRates);
        }
        if (!is_array($tiercomRates)) {
            $tiercomRates = array();
        }
        $udtcFixedConfig = $tiercomRates;
        if (is_array($udtcFixedConfig) && !empty($udtcFixedConfig)
            && !empty($udtcFixedConfig['limit']) && is_array($udtcFixedConfig['limit'])
        ) {
            reset($udtcFixedConfig['limit']);
            $firstTitleKey = key($udtcFixedConfig['limit']);
            if (!is_numeric($firstTitleKey)) {
                $newudtcFixedConfig = array();
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($udtcFixedConfig['limit'] as $_k => $_t) {
                    if ( ($_limit = $filter->filter($udtcFixedConfig['limit'][$_k]))
                        && false !== ($_value = $filter->filter($udtcFixedConfig['value'][$_k]))
                    ) {
                        $_limit = is_numeric($_limit) ? $_limit : '*';
                        $_sk    = is_numeric($_limit) ? $_limit : '9999999999';
                        $_sk    = 'str'.str_pad((string)$_sk, 20, '0', STR_PAD_LEFT);
                        $newudtcFixedConfig[$_sk] = array(
                            'limit' => $_limit,
                            'value' => $_value,
                        );
                    }
                }
                ksort($newudtcFixedConfig);
                $newudtcFixedConfig = array_values($newudtcFixedConfig);
                $tiercomRates = array_values($newudtcFixedConfig);
            }
        }
        if ($serialize) {
            if (is_array($tiercomRates)) {
                $tiercomRates = serialize($tiercomRates);
            }
        } else {
            if (is_string($tiercomRates)) {
                $tiercomRates = unserialize($tiercomRates);
            }
            if (!is_array($tiercomRates)) {
                $tiercomRates = array();
            }
        }
        $vendor->setData('tiercom_fixed_rates', $tiercomRates);
    }

    public function processPo($po)
    {
        $this->_processPoCommission($po);
        $this->_processPoTransactionFee($po);
    }

    protected function _processPoCommission($po)
    {
        $v = Mage::helper('udropship')->getVendor($po->getUdropshipVendor());
        $cFallbackMethod = Mage::helper('udropship')->getVendorFallbackField(
            $v, 'tiercom_fallback_lookup', 'udropship/tiercom/fallback_lookup'
        );
        $tierRates = $po->getUdropshipVendor() ? $this->getTiercomRates($po->getUdropshipVendor()) : array();
        $globalTierRates = $this->getGlobalTierComConfig();
        $topCats = Mage::helper('udtiercom')->getTopCategories();
        $catIdsToLoad = $catIds = array();
        $pIds = array();
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) continue;
            $pIds[] = $item->getProductId();
        }
        $products = Mage::getResourceModel('catalog/product_collection')->addIdFilter($pIds);
        if (($tcProdAttr = $this->getCommProductAttribute())) {
            $products->addAttributeToSelect($tcProdAttr->getAttributeCode());
        }
        foreach ($po->getAllItems() as $item) {
            $itemId = spl_object_hash($item);
            if ($item->getOrderItem()->getParentItem() || !($product = $products->getItemById($item->getProductId()))) continue;
            $_catIds = $product->getCategoryIds();
            if (empty($_catIds)) continue;
            reset($_catIds);
            $catIdsToLoad = array_merge($catIdsToLoad, $_catIds);
            $catIds[$itemId] = $_catIds;
        }
        $locale = Mage::app()->getLocale();
        $catIdsToLoad = array_unique($catIdsToLoad);
        $iCats = Mage::getResourceModel('catalog/category_collection')->addIdFilter($catIdsToLoad);
        $subcatMatchFlag = Mage::getStoreConfigFlag('udropship/tiercom/match_subcategories');
        $ratesToUse = array();
        foreach ($po->getAllItems() as $item) {
            $itemId = spl_object_hash($item);
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }

            if (($product = $products->getItemById($item->getProductId()))
                && $tcProdAttr
                && ''!==$product->getData($tcProdAttr->getAttributeCode())
                && null !==$product->getData($tcProdAttr->getAttributeCode())
            ) {
                $ratesToUse[$itemId]['value'] = $locale->getNumber(
                    $product->getData($tcProdAttr->getAttributeCode())
                );
            } elseif (!empty($catIds[$itemId])) {
                $exactMatched = $subcatMatched = false;
                $isGlobalTier = true;
                foreach ($catIds[$itemId] as $iCatId) {
                    if (!($iCat = $iCats->getItemById($iCatId))) continue;
                    $_exactMatched = $_subcatMatched = false;
                    $_isGlobalTier = true;
                    $_exactMatched = $topCats->getItemById($iCatId);
                    $catId = null;
                    if ($_exactMatched) {
                        $catId = $iCatId;
                    } elseif ($subcatMatchFlag) {
                        $_catPath = explode(',', Mage::helper('udropship/catalog')->getPathInStore($iCat));
                        foreach ($_catPath as $_catPathId) {
                            if ($topCats->getItemById($_catPathId)) {
                                $catId = $_catPathId;
                                $_subcatMatched = true;
                                break;
                            }
                        }
                    }
                    if ($catId && $topCats->getItemById($catId)) {
                        $_rateToUse = array();
                        if (isset($tierRates[$catId]) && isset($tierRates[$catId]['value']) && $tierRates[$catId]['value'] !== '') {
                            $_rateToUse['value'] = $tierRates[$catId]['value'];
                            $_isGlobalTier = false;
                        } else {
                            $_rateToUse['value'] = @$globalTierRates[$catId]['value'];
                            $_rateToUse['is_global_tier'] = true;
                        }
                        if ($_rateToUse['value']!==null && $_rateToUse['value']!==''
                            && (
                                !$_isGlobalTier && $isGlobalTier
                                || !$_isGlobalTier && ($_exactMatched || !$exactMatched)
                                || $_isGlobalTier && $isGlobalTier && ($_exactMatched || !$exactMatched)
                            )
                        ) {
                            $_rateToUse['value'] = $locale->getNumber($_rateToUse['value']);
                            $ratesToUse[$itemId] = $_rateToUse;
                        }
                    }
                    $exactMatched = $exactMatched || $_exactMatched;
                    $subcatMatched = $subcatMatched || $_subcatMatched;
                    $isGlobalTier = $isGlobalTier && $_isGlobalTier;
                }
            }

            if (!isset($ratesToUse[$itemId])
                || !empty($ratesToUse[$itemId]['is_global_tier'])
                    && $cFallbackMethod=='vendor'
                    && ''!==$v->getCommissionPercent()
                    && null!==$v->getCommissionPercent()
            ) {
                if (''!==$v->getCommissionPercent()
                    && null!==$v->getCommissionPercent()
                ) {
                    $ratesToUse[$itemId]['value'] = $locale->getNumber($v->getCommissionPercent());
                } else {
                    $ratesToUse[$itemId]['value'] = $locale->getNumber(Mage::getStoreConfig('udropship/tiercom/commission_percent'));
                }
            }
            $item->setCommissionPercent(@$ratesToUse[$itemId]['value']);
        }
        if (''!==$v->getCommissionPercent()
            && null!==$v->getCommissionPercent()
        ) {
            $poComPercent = $locale->getNumber($v->getCommissionPercent());
        } else {
            $poComPercent = $locale->getNumber(Mage::getStoreConfig('udropship/tiercom/commission_percent'));
        }
        $po->setCommissionPercent($poComPercent);
    }

    protected function _processItemTierTransactionFee($po)
    {
        $v = Mage::helper('udropship')->getVendor($po->getUdropshipVendor());
        $tierRates = $po->getUdropshipVendor() ? $this->getTiercomRates($po->getUdropshipVendor()) : array();
        $globalTierRates = $this->getGlobalTierComConfig();
        $topCats = Mage::helper('udtiercom')->getTopCategories();
        $catIdsToLoad = $catIds = array();
        $pIds = array();
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) continue;
            $pIds[] = $item->getProductId();
        }
        $products = Mage::getResourceModel('catalog/product_collection')->addIdFilter($pIds);
        if (($tcProdAttr = $this->getFixedRateProductAttribute())) {
            $products->addAttributeToSelect($tcProdAttr->getAttributeCode());
        }
        foreach ($po->getAllItems() as $item) {
            $itemId = spl_object_hash($item);
            if ($item->getOrderItem()->getParentItem() || !($product = $products->getItemById($item->getProductId()))) continue;
            $_catIds = $product->getCategoryIds();
            if (empty($_catIds)) continue;
            reset($_catIds);
            $catIdsToLoad = array_merge($catIdsToLoad, $_catIds);
            $catIds[$itemId] = $_catIds;
        }
        $locale = Mage::app()->getLocale();
        $catIdsToLoad = array_unique($catIdsToLoad);
        $iCats = Mage::getResourceModel('catalog/category_collection')->addIdFilter($catIdsToLoad);
        $subcatMatchFlag = Mage::getStoreConfigFlag('udropship/tiercom/match_subcategories');
        $ratesToUse = array();
        foreach ($po->getAllItems() as $item) {
            $itemId = spl_object_hash($item);
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }

            if (($product = $products->getItemById($item->getProductId()))
                && $tcProdAttr
                && ''!==$product->getData($tcProdAttr->getAttributeCode())
                && null !==$product->getData($tcProdAttr->getAttributeCode())
            ) {
                $ratesToUse[$itemId]['fixed'] = $locale->getNumber(
                    $product->getData($tcProdAttr->getAttributeCode())
                );
            } elseif (!empty($catIds[$itemId])) {
                $exactMatched = $subcatMatched = false;
                foreach ($catIds[$itemId] as $iCatId) {
                    if (!($iCat = $iCats->getItemById($iCatId))) continue;
                    $_exactMatched = $_subcatMatched = false;
                    $_exactMatched = $topCats->getItemById($iCatId);
                    $catId = null;
                    if ($_exactMatched) {
                        $catId = $iCatId;
                    } elseif ($subcatMatchFlag) {
                        $_catPath = explode(',', Mage::helper('udropship/catalog')->getPathInStore($iCat));
                        foreach ($_catPath as $_catPathId) {
                            if ($topCats->getItemById($_catPathId)) {
                                $catId = $_catPathId;
                                $_subcatMatched = true;
                                break;
                            }
                        }
                    }
                    if ($catId && $topCats->getItemById($catId)
                        && ($_exactMatched || !$exactMatched)
                    ) {
                        $_rateToUse = array();
                        $_rateToUse['fixed'] = isset($tierRates[$catId]) && isset($tierRates[$catId]['fixed']) && $tierRates[$catId]['fixed'] !== ''
                            ? $tierRates[$catId]['fixed']
                            : @$globalTierRates[$catId]['fixed'];
                        $_rateToUse['fixed'] = $locale->getNumber($_rateToUse['fixed']);
                        $ratesToUse[$itemId] = $_rateToUse;
                    }
                    $exactMatched = $exactMatched || $_exactMatched;
                    $subcatMatched = $subcatMatched || $_subcatMatched;
                }
            }

            if (!empty($ratesToUse[$itemId]['fixed'])) {
                $item->setTransactionFee($item->getTransactionFee()+$item->getQty()*$ratesToUse[$itemId]['fixed']);
            }
        }
    }

    protected function _processPoTransactionFee($po)
    {
        $poTransFee = 0;
        $vendor = Mage::helper('udropship')->getVendor($po->getUdropshipVendor());
        if ($this->isFlatCalculation($vendor)) {
            if (''!=$vendor->getTransactionFee()) {
                $poTransFee = Mage::app()->getLocale()->getNumber($vendor->getTransactionFee());
            } else {
                $poTransFee = Mage::app()->getLocale()->getNumber(Mage::getStoreConfig('udropship/tiercom/transaction_fee'));
            }
        }
        foreach ($po->getAllItems() as $item) {
            $item->setTransactionFee(0);
        }
        $this->_processItemTierTransactionFee($po);
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) continue;
            $this->_processItemRuleTransactionFee($item);
        }
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) continue;
            $poTransFee += $item->getTransactionFee();
        }
        $po->setTransactionFee($poTransFee);
    }

    protected function _processItemTransactionFee($item)
    {
        return $this->_processItemRuleTransactionFee($item);
    }
    protected function _processItemRuleTransactionFee($item)
    {
        $oItem = $item->getOrderItem();
        $vId = $item->getPo()
            ? $item->getPo()->getUdropshipVendor()
            : ($item->getShipment() ? $item->getShipment()->getUdropshipVendor() : false);
        $tierRates = $vId ? $this->getTiercomFixedRates($vId) : array();
        $fixedRule = $vId ? $this->getTiercomFixedRule($vId) : false;
        $globalTierRates = $this->getGlobalTierComFixedConfig();
        $globalFixedRule = $this->getGlobalTierComFixedRule();
        $_tierConfig = $fixedRule && !empty($tierRates) ? $tierRates : $globalTierRates;
        $_tierRule = $fixedRule && !empty($tierRates) ? $fixedRule : $globalFixedRule;
        if (is_array($_tierConfig) && !empty($_tierConfig)) {
            $ruleValue = null;
            $multiQty = false;
            switch ($_tierRule) {
                case 'item_price':
                    $ruleValue = $oItem->getBasePrice()+$oItem->getBaseTaxAmount()/$oItem->getQtyOrdered()-$oItem->getBaseDiscountAmount()/$oItem->getQtyOrdered();
                    $multiQty = true;
                    break;
            }
            if (!is_null($ruleValue)) {
                foreach ($_tierConfig as $hc) {
                    if (!isset($hc['limit']) || !isset($hc['value'])) continue;
                    if (is_numeric($hc['limit']) && $ruleValue<=$hc['limit']
                        || !is_numeric($hc['limit'])
                    ) {
                        $fixedFee = $hc['value'];
                        if ($multiQty) {
                            $fixedFee = $fixedFee*$item->getQty();
                        }
                        break;
                    }
                }
                if (isset($fixedFee)) {
                    return $item->setTransactionFee($item->getTransactionFee()+$fixedFee);
                }
            }
        }
        return 0;
    }

    public function getTiercomRates($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $value = $vendor->getTiercomRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getTiercomFixedRates($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $value = $vendor->getTiercomFixedRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getTiercomFixedRule($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $value = $vendor->getTiercomFixedRule();
        return $value;
    }

    public function isTierCalculation($vendor)
    {
        return $this->_isCalculation('tier', $vendor);
    }
    public function isFlatCalculation($vendor)
    {
        return $this->_isCalculation('flat', $vendor);
    }
    public function isRuleCalculation($vendor)
    {
        return $this->_isCalculation('rule', $vendor);
    }

    protected function _isCalculation($type, $vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $cfgValue = $vendor->getTiercomFixedCalcType();
        if ($cfgValue=='') {
            $cfgValue = Mage::getStoreConfig('udropship/tiercom/fixed_calculation_type');
        }
        return false!==strpos($cfgValue, $type);
    }

    public function getGlobalTierComConfig()
    {
        $value = Mage::getStoreConfig('udropship/tiercom/rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getGlobalTierComFixedRule()
    {
        return Mage::getStoreConfig('udropship/tiercom/fixed_rule');
    }

    public function getGlobalTierComFixedConfig()
    {
        $value = Mage::getStoreConfig('udropship/tiercom/fixed_rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    protected $_topCats;
    public function getTopCategories()
    {
        if (null === $this->_topCats) {
            $cHlp = Mage::helper('udropship/catalog');
            $topCatId = Mage::getStoreConfig('udropship/tiercom/tiered_category_parent');
            $topCat = Mage::getModel('catalog/category')->load($topCatId);
            if (!$topCat->getId()) {
                $topCat = $cHlp->getStoreRootCategory();
            }
            $this->_topCats = $cHlp->getCategoryChildren(
                $topCat
            );
        }
        return $this->_topCats;
    }
}
