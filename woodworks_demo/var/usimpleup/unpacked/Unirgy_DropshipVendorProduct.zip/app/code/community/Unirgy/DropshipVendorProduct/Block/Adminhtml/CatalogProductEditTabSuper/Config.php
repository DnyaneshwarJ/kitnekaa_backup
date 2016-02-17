<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_CatalogProductEditTabSuper_Config extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('udprod/catalogProductEditSuper/config.phtml');
    }
}
