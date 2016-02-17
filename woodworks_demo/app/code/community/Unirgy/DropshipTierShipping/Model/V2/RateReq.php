<?php

class Unirgy_DropshipTierShipping_Model_V2_RateReq extends Varien_Object
{
    protected $_cachedRates = array();
    public function getResult()
    {
        $tsHlp = Mage::helper('udtiership');
        $rateRes = new Unirgy_DropshipTierShipping_Model_V2_RateRes(array(
            'category_id' => $this->getCategoryId(),
            'vendor_ship_class' => $this->getVendorShipClass(),
            'customer_ship_class' => $this->getCustomerShipClass(),
            'product_id' => $this->getProduct()->getId(),
            'product_name' => $this->getProduct()->getName(),
        ));

        $vendor = $this->getVendor();
        $store = $this->getStore();

        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();

        $extraCond = array();

        $cscId = $this->getCustomerShipClass();
        if (!is_array($cscId)) {
            $cscId = array($cscId);
        }
        $vscId = $this->getVendorShipClass();
        if (!is_array($vscId)) {
            $vscId = array($vscId);
        }
        $cgIds = $this->getCustomerGroupId();

        $catId = $this->getCategoryId();
        if (!is_array($catId)) {
            $catId = array($catId);
        }

        $cscCond = array();
        foreach ($cscId as $_cscId) {
            $cscCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_shipclass_id)',$_cscId);
        }
        $cgCond = array();
        foreach ($cgIds as $_cgId) {
            $cgCond[] = $conn->quoteInto('FIND_IN_SET(?,customer_group_id)',$_cgId);
        }
        if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
            $extraCond[] = '( '.implode(' OR ', $cscCond).' ) AND ('.implode(' OR ', $cgCond).' ) ';
        } else {
            $extraCond[] = '( '.implode(' OR ', $cscCond).' ) ';
        }
        $extraCond['__order'] = array(
            Mage::helper('udropship/catalog')->getCaseSql('customer_shipclass_id', array_flip($cscCond), 9999),
        );
        if (Mage::getStoreConfigFlag('carriers/udtiership/use_customer_group')) {
            $extraCond['__order'][] = Mage::helper('udropship/catalog')->getCaseSql('customer_group_id', array_flip($cgCond), 9999);
        }

        if (!$vendor || !$vendor->getData('tiership_use_v2_rates')) {
            $vscCond = array(
                $conn->quoteInto('FIND_IN_SET(?,vendor_shipclass_id)','*')
            );
            foreach ($vscId as $_vscId) {
                $vscCond[] = $conn->quoteInto('FIND_IN_SET(?,vendor_shipclass_id)',$_vscId);
            }
            $extraCond[] = '( '.implode(' OR ', $vscCond).' ) ';
        }

        $catCond = array(
            $conn->quoteInto('FIND_IN_SET(?,category_ids)','*')
        );
        foreach ($catId as $_catId) {
            $catCond[] = $conn->quoteInto('FIND_IN_SET(?,category_ids)',$_catId);
        }
        $extraCond[] = '( '.implode(' OR ', $catCond).' ) ';

        $cacheKey = implode('-', array(
            intval($vendor && $vendor->getData('tiership_use_v2_rates')),
            $this->getDeliveryType(),
            serialize($extraCond)
        ));
        $tierRates = array();

        if (array_key_exists($cacheKey, $this->_cachedRates)) {
            $tierRates = $this->_cachedRates[$cacheKey];
        } else {
            if ($vendor && $vendor->getData('tiership_use_v2_rates')) {
                $tierRates = $tsHlp->getVendorV2Rates($vendor->getId(), $this->getDeliveryType(), $extraCond);
            } else {
                $tierRates = $tsHlp->getV2Rates($this->getDeliveryType(), $extraCond);
            }
            $this->_cachedRates[$cacheKey] = $tierRates;
        }
        if (empty($tierRates)) {
            return false;
        }
        reset($tierRates);
        $tierRate = current($tierRates);

        foreach ($this->getSubkeys() as $sk) {
            $_specData = array();
            $value = $this->getUdmultiRate($sk);
            if ($this->_isRateEmpty($value)) {
                $value = $this->getProductRate($sk);
                if ($value===false) {
                    return false;
                } elseif ($this->_isRateEmpty($value)) {
                    $value = $this->getLocale()->getNumber(@$tierRate[$sk]);
                    if (!$tsHlp->isCtCustomPerCustomerZone($sk, $store)) {
                        $skExtra = $sk.'_extra';
                        $extra = array();
                        if (isset($tierRate[$skExtra])) {
                            $extra = $tierRate[$skExtra];
                            if (!is_array($extra)) {
                                $extra = Mage::helper('udropship')->unserialize($extra);
                                if (is_array($extra)) {
                                    usort($extra, array($this, 'sortBySortOrder'));
                                }
                            }
                            if (!is_array($extra)) {
                                $extra = array();
                            }
                        }
                        $surcharge = false;
                        foreach ($extra as $__e) {
                            if (isset($__e['customer_shipclass_id'])) {
                                $_curCSC = $__e['customer_shipclass_id'];
                                if (!is_array($_curCSC)) {
                                    $_curCSC = array($_curCSC);
                                }
                                if (array_intersect($_curCSC,$cscId)) {
                                    $surcharge = $this->getLocale()->getNumber(@$__e['surcharge']);
                                    break;
                                }
                            }
                        }
                        if ($tsHlp->isCtPercentPerCustomerZone($sk, $store) && $surcharge!==false) {
                            $value = $value + $value*$surcharge/100;
                        } elseif ($tsHlp->isCtFixedPerCustomerZone($sk, $store) && $surcharge!==false) {
                            $value = $value + $surcharge;
                        }
                    }
                } else {
                    $_specData['is_product'] = true;
                }
            } else {
                $_specData['is_udmulti'] = true;
            }
            $rateRes->setData($sk, $this->getLocale()->getNumber($value));
            $rateRes->setData($rateRes->specPrefix().$sk, $_specData);
        }
        return $rateRes;
    }

    protected function _isRateEmpty($value)
    {
        return null===$value||false===$value||''===$value;
    }

    public function getUdmultiRate($sk)
    {
        $value = '';
        if ($this->getVendor()
            && ($vId = $this->getVendor()->getId())
            && ($mv = $this->getProduct()->getMultiVendorData($vId))
            && $mv['vendor_id'] == $vId
        ) {
            if (!empty($mv['freeshipping'])) {
                $value = 0;
            } elseif (in_array($sk, array('cost'))) {
                if (null !== @$mv['shipping_price'] && '' !== @$mv['shipping_price']) {
                    $value = $this->getLocale()->getNumber(@$mv['shipping_price']);
                }
            }
        }
        return $value;
    }
    public function getProductRate($sk)
    {
        $tsHlp = Mage::helper('udtiership');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();
        $value = '';
        if ($this->getProduct()->getUdtiershipUseCustom()) {
            $value = false;
            $cscId = $this->getCustomerShipClass();
            $dt = $this->getDeliveryType();
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
                $extraCond = array(
                    '( '.implode(' OR ', $cscCond).' ) '
                );
                $extraCond['__order'] = Mage::helper('udropship/catalog')->getCaseSql('customer_shipclass_id', array_flip($cscCond), 9999);
            }
            $pRates = $tsHlp->getProductV2Rates($this->getProduct(), $dt, $extraCond);
            foreach ($pRates as $pRate) {
                if (!isset($pRate['delivery_type']) || !isset($pRate['customer_shipclass_id'])) continue;
                $__cscId = $pRate['customer_shipclass_id'];
                if (!is_array($__cscId)) {
                    $__cscId = array($__cscId);
                }
                $__dt = $pRate['delivery_type'];
                if (!is_array($__dt)) {
                    $__dt = array($__dt);
                }
                if (array_intersect($__cscId,$cscId)
                    && array_intersect($__dt,$dt)
                ) {
                    $value = $this->getLocale()->getNumber(@$pRate[$sk]);
                    break;
                }
            }
        }
        return $value;
    }

    public function init($catId, $vscId, $cscId, $cgId)
    {
        $tsHlp = Mage::helper('udtiership');
        $this->setCategoryId($catId);
        $this->setVendorShipClass($vscId);
        $this->setCustomerShipClass($cscId);
        $this->setCustomerGroupId($cgId);
        return $this;
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

}