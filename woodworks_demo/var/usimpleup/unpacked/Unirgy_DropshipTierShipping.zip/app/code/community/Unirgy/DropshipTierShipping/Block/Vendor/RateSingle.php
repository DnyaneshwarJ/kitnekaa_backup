<?php

class Unirgy_DropshipTierShipping_Block_Vendor_RateSingle extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('unirgy/tiership/vendor/rate_single.phtml');
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
        if ($this->hasMaxKey()) {
            $k = array($this->getDataObject(), $this->getKey());
        } else {
            $k = array($this->getGlobalDataObject(), $this->getGlobKey());
        }
        $value = $k[0]->getData($k[1].'/'.$this->getSubkey(), false);
        return (string)$value;
    }

    public function initKey($key, $globKey=false)
    {
        $this->setKey($key);
        if ($globKey===false) $globKey=$key;
        $this->setGlobKey($globKey);
        return $this;
    }

    protected $_subkeyDef = array('align', 'subkey_type', 'subkey', 'max_key');
    public function initSubkey($skType)
    {
        $skTypeCnt = count($skType);
        while ($skTypeCnt++<count($this->_subkeyDef)) {
            $skType[] = false;
        }
        $sk = array_combine($this->_subkeyDef, $skType);
        $this->addData($sk);
        return $this;
    }

    public function hasMaxKey()
    {
        return $this->getData('max_key')!==false;
    }

    public function getMaxValue()
    {
        if (!$this->hasMaxKey()) return false;
        return (string)$this->getGlobalDataObject()->getData(sprintf('%s/%s',
            $this->getKey(), $this->getData('max_key')
        ));
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

    public function formatedValue()
    {
        $tsHlp = Mage::helper('udtiership');
        $format = '%s';
        $formated = '';
        $skType = $this->getSubkeyType();
        $store = $this->getStore();
        $isBase = $this->getIsBaseRate();
        if ($this->getValue() === null || $this->getValue() === '') {
            return '';
        }
        switch ($skType) {
            case 'cost':
            case 'additional':
                if (!$isBase && $tsHlp->isCtPercentPerCustomerZone($skType, $store)) {
                    $format = '%s [%% to base]';
                } elseif (!$isBase && $tsHlp->isCtFixedPerCustomerZone($skType, $store)) {
                    $format = '%s [fixed to base]';
                } else {
                    $formated = $store->formatPrice($this->getValue());
                }
                break;
            case 'handling':
                if (!$tsHlp->isApplyMethodNone($skType, $store)) {
                    if (!$isBase && $tsHlp->isCtPercentPerCustomerZone($skType, $store)) {
                        $format = '%s [%% to base]';
                    } elseif (!$isBase && $tsHlp->isCtFixedPerCustomerZone($skType, $store)) {
                        $format = '%s [fixed to base]';
                    } else {
                        if ($tsHlp->isApplyMethodPercent($skType, $store)) {
                            $format = '%s%%';
                        } else {
                            $formated = $store->formatPrice($this->getValue());
                        }
                    }
                }
                break;
        }
        return !$formated && $this->getValue() !== null && $this->getValue() !== ''
            ? sprintf($format, $this->getValue())
            : $formated;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }
}