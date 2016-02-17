<?php

class Unirgy_Dropship_Model_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    public function getHelper()
    {
        return Mage::helper('udropship/wysiwyg_images');
    }
    public function getSession()
    {
        return Mage::getSingleton('udropship/session');
    }
}