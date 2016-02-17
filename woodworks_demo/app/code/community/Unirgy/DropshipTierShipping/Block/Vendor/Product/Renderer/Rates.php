<?php


class Unirgy_DropshipTierShipping_Block_Vendor_Product_Renderer_Rates extends Unirgy_DropshipTierShipping_Block_ProductAttribute_Renderer_Rates
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/tiership/vendor/v2/product/rates.phtml');
    }
}