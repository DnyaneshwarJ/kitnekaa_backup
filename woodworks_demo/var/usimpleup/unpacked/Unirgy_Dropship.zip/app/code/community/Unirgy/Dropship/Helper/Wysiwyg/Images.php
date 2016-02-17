<?php

class Unirgy_Dropship_Helper_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{
    public function getStorageRoot()
    {
        $udSess = Mage::getSingleton('udropship/session');
        return Mage::getConfig()->getOptions()->getMediaDir() . DS . Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY
            . DS . 'udvendor-'.$udSess->getVendorId();
    }
    public function isUsingStaticUrlsAllowed()
    {
        return true;
    }
}