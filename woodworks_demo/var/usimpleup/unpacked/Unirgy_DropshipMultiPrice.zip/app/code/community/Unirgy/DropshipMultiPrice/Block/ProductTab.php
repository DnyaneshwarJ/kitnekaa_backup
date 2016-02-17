<?php

class Unirgy_DropshipMultiPrice_Block_ProductTab extends Mage_Catalog_Block_Product_View_Description
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('product.vendors',
            $this->getLayout()->createBlock('udmultiprice/productVendors', 'product.vendors')->setTemplate('udmultiprice/product/vendors.phtml')
        );
        return $this;
    }
}
