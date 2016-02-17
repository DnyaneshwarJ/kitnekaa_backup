<?php

class Unirgy_DropshipTierShipping_Model_V2_RateRes extends Varien_Object
{
    protected $_specPrefix = '__specific_';
    public function specPrefix()
    {
        return $this->_specPrefix;
    }
    public function isProductRate($subkey)
    {
        return $this->getData($this->_specPrefix.$subkey.'/is_product')
        || $this->getData($this->_specPrefix.$subkey.'/is_udmulti');
    }
    public function isFallbackRate($subkey)
    {
        return $this->getData($this->_specPrefix.$subkey.'/is_fallback');
    }
    public function isVendorRate($subkey)
    {
        return $this->getData($this->_specPrefix.$subkey.'/is_vendor');
    }
    public function isGlobalRate($subkey)
    {
        return $this->getData($this->_specPrefix.$subkey.'/is_global');
    }
    public function isCategoryRate($subkey)
    {
        return !$this->isFallbackRate($subkey) && !$this->isProductRate($subkey) ;
    }
}