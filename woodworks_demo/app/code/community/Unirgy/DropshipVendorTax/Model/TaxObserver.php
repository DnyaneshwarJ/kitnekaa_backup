<?php

class Unirgy_DropshipVendorTax_Model_TaxObserver extends Mage_Tax_Model_Observer
{
    public function addTaxPercentToProductCollection($observer)
    {
        $helper = Mage::helper('tax');
        $collection = $observer->getEvent()->getCollection();
        $store = $collection->getStoreId();
        if (!$helper->needPriceConversion($store)) {
            return $this;
        }

        if ($collection->requireTaxPercent()) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest();
            foreach ($collection as $item) {
                if (null === $item->getTaxClassId()) {
                    $item->setTaxClassId($item->getMinimalTaxClassId());
                }
                Mage::helper('udtax')->setVendorClassId($request, $item);
                $tciKey = $item->getTaxClassId().'-'.$request->getVendorClassId();
                if (!isset($classToRate[$tciKey])) {
                    $request->setProductClassId($item->getTaxClassId());
                    $classToRate[$tciKey] = Mage::getSingleton('tax/calculation')->getRate($request);
                }
                $item->setTaxPercent($classToRate[$tciKey]);
            }

        }
        return $this;
    }
}