<?php

class Unirgy_DropshipMulti_Model_Mysql4_Stock extends Mage_CatalogInventory_Model_Mysql4_Stock
{
    public function getProductsStock($stock, $productIds, $lockRows = false)
    {
        $rows = parent::getProductsStock($stock, $productIds, $lockRows);
        $vCollection = Mage::helper('udmulti')->getMultiVendorData($productIds);
        $udmArr = $udmAvail = array();
        foreach ($vCollection as $vp) {
            $udmArr[$vp->getProductId()][$vp->getVendorId()] = $vp->getStockQty();
            $udmAvail[$vp->getProductId()][$vp->getVendorId()] = array(
                'product_id' => $vp->getProductId(),
                'avail_state' => $vp->getData('avail_state'),
                'avail_date' => $vp->getData('avail_date'),
                'status' => $vp->getData('status'),
            );
        }
        foreach ($rows as &$p) {
            $pId = $p['product_id'];
            $arr = !empty($udmArr[$pId]) ? $udmArr[$pId] : array();
            $avail = !empty($udmAvail[$pId]) ? $udmAvail[$pId] : array();
            $p['udmulti_stock'] = $arr;
            $p['udmulti_avail'] = $avail;
        }
        return $rows;
    }
}