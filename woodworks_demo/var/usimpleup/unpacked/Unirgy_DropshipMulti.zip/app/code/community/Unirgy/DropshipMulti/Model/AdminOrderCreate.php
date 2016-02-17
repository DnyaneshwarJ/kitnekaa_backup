<?php

class Unirgy_DropshipMulti_Model_AdminOrderCreate extends Mage_Adminhtml_Model_Sales_Order_Create
{
    public function updateQuoteItems($data)
    {
        $iHlp = Mage::helper('udropship/item');
        if (is_array($data)) {
            $unsetIds = array();
            foreach ($data as $itemId => $info) {
                $item = $this->getQuote()->getItemById($itemId);
                $parentId = $item->getParentItemId();
                $skipUpdate = !empty($info['configured']) || !empty($info['action']);
                if ($parentId && !empty($data[$parentId])) {
                    $skipUpdate = $skipUpdate || !empty($data[$parentId]['configured']) || !empty($data[$parentId]['action']);
                }
                if ($item && !empty($info['udropship_vendor']) && !$skipUpdate
                    && $info['udropship_vendor']!=$item->getUdropshipVendor()
                ) {
                    $iHlp->deleteForcedVendorIdOption($item);
                    $iHlp->setUdropshipVendor($item, $info['udropship_vendor']);
                    $iHlp->setForcedVendorIdOption($item, $info['udropship_vendor']);
                    if ($item->getHasChildren()) {
                        $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                        foreach ($children as $child) {
                            $iHlp->deleteForcedVendorIdOption($child);
                            $iHlp->setUdropshipVendor($child, $info['udropship_vendor']);
                            $iHlp->setForcedVendorIdOption($child, $info['udropship_vendor']);
                        }
                    }
                    $item->setSkipForcedVendorStockCheck(true);
                } elseif ($parentId && $skipUpdate) {
                    $unsetIds[$itemId] = $itemId;
                }
            }
            foreach ($unsetIds as $unsetId) {
                unset($data[$unsetId]);
            }
        }
        return parent::updateQuoteItems($data);
    }
    public function saveQuote()
    {
        parent::saveQuote();
        try {
            $hlp = Mage::helper('udropship/protected');
            $items = $this->getQuote()->getAllItems();
            //$hlp->setAllowReorginizeQuote(true);
            $hlp->startAddressPreparation($items);
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
            //$hlp->setAllowReorginizeQuote(false);
        } catch (Exception $e) {
            // all necessary actions should be already done by now, kill the exception
        }
        return $this;
    }
}
