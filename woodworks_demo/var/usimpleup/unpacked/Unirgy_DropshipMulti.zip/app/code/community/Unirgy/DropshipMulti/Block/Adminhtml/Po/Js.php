<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
class Unirgy_DropshipMulti_Block_Adminhtml_Po_Js extends Unirgy_DropshipMulti_Block_Adminhtml_Order_Js
{
    public function checkVendors()
    {
        $items = $this->getOrder()->getAllItems();
        Mage::helper('udropship/protected')->reassignApplyStockAvailability($items);

        foreach ($items as $item) {
            if ($item->getProductType()=='configurable') {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    foreach ($child->getUdropshipStockLevels() as $vId=>$status) {
                        $result['stock'][$item->getId()][$vId] = $status;
                    }
                    break;
                }
            } else {
                foreach ($item->getUdropshipStockLevels() as $vId=>$status) {
                    $result['stock'][$item->getId()][$vId] = $status;
                }
            }
        }
    }
}