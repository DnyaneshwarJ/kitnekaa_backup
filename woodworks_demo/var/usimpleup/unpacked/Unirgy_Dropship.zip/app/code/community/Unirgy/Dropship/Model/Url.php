<?php

class Unirgy_Dropship_Model_Url extends Mage_Core_Model_Url
{
    public function getStore()
    {
        if (!$this->hasData('store')) {
            $this->setStore(null);
        }
        return Mage::registry('url_store')
            ? Mage::registry('url_store')
            : $this->_getData('store');
    }
}