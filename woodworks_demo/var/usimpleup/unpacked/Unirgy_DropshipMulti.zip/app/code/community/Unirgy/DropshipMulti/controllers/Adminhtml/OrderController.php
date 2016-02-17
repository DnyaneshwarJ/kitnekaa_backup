<?php

class Unirgy_DropshipMulti_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    public function updateVendorsAction()
    {
        try {
            // get parameters
            $orderId = $this->getRequest()->getParam('order_id');
            $vendors = $this->getRequest()->getParam('vendors');
            $hlp = Mage::helper('udmulti');

            if (!$this->getRequest()->getPost() || !$orderId || !$vendors) {
                Mage::throwException(Mage::helper('udropship')->__('Invalid parameters.'));
            }

            $order = Mage::getModel('sales/order')->load($orderId);
            $storeId = $order->getStoreId();

            if (!Mage::helper('udropship')->isUdropshipOrder($order)) {
                Mage::app()->getResponse()->setBody('<span class="error">Order is not dropshippable</span>');
                return;
            }
            
            $items = $order->getAllItems();

            Mage::helper('udropship/protected')->reassignApplyStockAvailability($items);
            
            $hasOutOfStockError = '';
            foreach ($items as $item) {
                if ($item->isDummy(true)) continue;
                if ($item->getProductType()=='configurable') {
                    $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                    foreach ($children as $child) {
                        $vendors[$child->getId()]['id'] = $vendors[$item->getId()]['id'];
                        if (Mage::helper('udropship')->getItemStockCheckQty($child)
                            && !$child->getData("udropship_stock_levels/{$vendors[$item->getId()]['id']}/status")
                            && $child->getUdropshipVendor()!=$vendors[$item->getId()]['id']
                        ) {
                            $hasOutOfStockError .= Mage::helper('udropship')->__(
                                "%s x %s is not available at vendor '%s'",
                                Mage::helper('udropship')->getItemStockCheckQty($child), $child->getSku(),
                                Mage::helper('udropship')->getVendorName($vendors[$item->getId()]['id'])
                            )."\n";
                        }
                        break;
                    }
                } else {
                    if (Mage::helper('udropship')->getItemStockCheckQty($item)>0
                        && !$item->getData("udropship_stock_levels/{$vendors[$item->getId()]['id']}/status")
                        && $item->getUdropshipVendor()!=$vendors[$item->getId()]['id']
                    ) {
                        $hasOutOfStockError .= Mage::helper('udropship')->__(
                            "%s x %s is not available at vendor '%s'",
                            Mage::helper('udropship')->getItemStockCheckQty($item), $item->getSku(),
                            Mage::helper('udropship')->getVendorName($vendors[$item->getId()]['id'])
                        )."\n";
                    }
                }
            }
            
            if (!empty($hasOutOfStockError)) Mage::throwException(trim($hasOutOfStockError));

            $result = $hlp->updateOrderItemsVendors($orderId, $vendors);

            if ($result) {
                $msg = '<span class="success">'.Mage::helper('udropship')->__('The vendors have been updated successfully.').'</span>';
            } else {
                $msg = '<span class="notice">'.Mage::helper('udropship')->__('No changes were neccessary.').'</span>';
            }
        } catch (Exception $e) {
            $msg = '<span class="error">'.$e->getMessage().'</span>';
        }
        Mage::app()->getResponse()->setBody($msg);
    }

    public function checkVendorsAction()
    {
        $result = array();
        try {
            // get parameters
            $orderId = $this->getRequest()->getParam('order_id');
            $vendors = $this->getRequest()->getParam('vendors');
            $hlp = Mage::helper('udmulti');

            if (!$this->getRequest()->getPost() || !$orderId || !$vendors) {
                Mage::throwException(Mage::helper('udropship')->__('Invalid parameters.'));
            }

            $items = Mage::getModel('sales/order')->load($orderId)
                ->getAllItems();
/*
            $items = Mage::getModel('sales/order_item')->getCollection()
                ->addFieldToFilter('item_id', array('in'=>array_keys($vendors)));
*/
            Mage::helper('udropship/protected')->reassignApplyStockAvailability($items);
            /*
            Mage::getSingleton('udmulti/method_abstract')
                ->collectStockLevels($items);
                */
            $availability = Mage::getSingleton('udropship/stock_availability');

            foreach ($items as $item) {
                if ($item->getProductType()=='configurable') {
                    $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                    foreach ($children as $child) {
                        foreach ($child->getUdropshipStockLevels() as $vId=>$status) {
                            $result['stock'][$item->getId()][$vId] = $status || $item->getUdropshipVendor()==$vId;
                        }
                        break;
                    }
                } else {
                    foreach ($item->getUdropshipStockLevels() as $vId=>$status) {
                        $result['stock'][$item->getId()][$vId] = $status || $item->getUdropshipVendor()==$vId;
                    }
                }
            }

            $result['message'] = '<span class="success">'.Mage::helper('udropship')->__('The vendors stock has been checked successfully.').'</span>';
        } catch (Exception $e) {
            $result['message'] = '<span class="error">'.$e->getMessage().'</span>';
        }
        Mage::app()->getResponse()->setBody(Zend_Json::encode($result));
    }
}