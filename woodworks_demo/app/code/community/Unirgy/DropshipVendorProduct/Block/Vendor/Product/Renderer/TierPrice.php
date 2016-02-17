<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_TierPrice extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier
{
    public function __construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/tier_price.phtml');
    }
}
