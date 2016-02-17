<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_SystemConfigField_RateSingle extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiership/system/form_field/rate_single.phtml');
        }
    }

    public function getName()
    {
        return sprintf('%s[%s][%s]',
            $this->getBaseName(), $this->getKey(), $this->getSubkey()
        );
    }
    public function getValue()
    {
        $dataObj = $this->getDataObject();
        $aKey = sprintf('%s/%s',
            $this->getKey(), $this->getSubkey()
        );
        $value = $dataObj->getData($aKey, false);
        return (string)$value;
    }

    public function getSuffix()
    {
        $tsHlp = Mage::helper('udtiership');
        $suffix = '';
        $skType = $this->getSubkeyType();
        $store = $this->getStore();
        $isBase = $this->getIsBaseRate();
        switch ($skType) {
            case 'cost':
            case 'additional':
                if (!$isBase && $tsHlp->isCtPercentPerCustomerZone($skType, $store)) {
                    $suffix = '% to base';
                } elseif (!$isBase && $tsHlp->isCtFixedPerCustomerZone($skType, $store)) {
                    $suffix = 'fixed to base';
                } else {
                    $suffix = (string)$store->getBaseCurrencyCode();
                }
                break;
            case 'handling':
                if (!$tsHlp->isApplyMethodNone($skType, $store)) {
                    if (!$isBase && $tsHlp->isCtPercentPerCustomerZone($skType, $store)) {
                        $suffix = '% to base';
                    } elseif (!$isBase && $tsHlp->isCtFixedPerCustomerZone($skType, $store)) {
                        $suffix = 'fixed to base';
                    } else {
                        if ($tsHlp->isApplyMethodPercent($skType, $store)) {
                            $suffix = '%';
                        } else {
                            $suffix = (string)$store->getBaseCurrencyCode();
                        }
                    }
                }
                break;
        }
        return $suffix;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }
}