<?php

class Unirgy_DropshipVendorPromotions_Block_Adminhtml_PromoWidgetChooserSku extends Mage_Adminhtml_Block_Promo_Widget_Chooser_Sku
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(0)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id')
            ->addAttributeToFilter('udropship_vendor', Mage::getSingleton('udropship/session')->getVendorId());

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}