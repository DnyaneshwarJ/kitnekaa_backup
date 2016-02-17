<?php

class Unirgy_Dropship_Model_BundleProductType extends Mage_Bundle_Model_Product_Type
{
    public function checkProductBuyState($product = null)
    {
        Mage_Catalog_Model_Product_Type_Abstract::checkProductBuyState($product);
        $product            = $this->getProduct($product);
        $productOptionIds   = $this->getOptionsIds($product);
        $productSelections  = $this->getSelectionsCollection($productOptionIds, $product);
        $selectionIds       = $product->getCustomOption('bundle_selection_ids');
        $selectionIds       = unserialize($selectionIds->getValue());
        $buyRequest         = $product->getCustomOption('info_buyRequest');
        $buyRequest         = new Varien_Object(unserialize($buyRequest->getValue()));
        $bundleOption       = $buyRequest->getBundleOption();

        if (empty($bundleOption)) {
            Mage::throwException($this->getSpecifyOptionMessage());
        }

        $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
        foreach ($selectionIds as $selectionId) {
            /* @var $selection Mage_Bundle_Model_Selection */
            $selection = $productSelections->getItemById($selectionId);
            if (!$selection || (!$selection->isSalable() && !$skipSaleableCheck)) {
                Mage::throwException(
                    Mage::helper('udropship')->__('Selected required options are not available.')
                );
            }
        }

        /*
        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $optionsCollection = $this->getOptionsCollection($product);
        foreach ($optionsCollection->getItems() as $option) {
            if ($option->getRequired() && empty($bundleOption[$option->getId()])) {
                Mage::throwException(
                    Mage::helper('udropship')->__('Required options are not selected.')
                );
            }
        }
        */

        return $this;
    }
}