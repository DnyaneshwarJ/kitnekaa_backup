<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_SystemConfigField_SimpleRateSingle extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiership/system/form_field/simple_rate_single.phtml');
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
        switch ($skType) {
            case 'cost':
            case 'additional':
                $suffix = (string)$store->getBaseCurrencyCode();
                break;
                break;
        }
        return $suffix;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }
}