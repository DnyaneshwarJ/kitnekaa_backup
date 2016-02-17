<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_GroupPrice extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
{
    public function __construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/group_price.phtml');
    }
}
