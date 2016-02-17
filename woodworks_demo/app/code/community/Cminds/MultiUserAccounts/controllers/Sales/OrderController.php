<?php
/**
 * Sales orders controller
 *
 * @author      CreativeMindsSolutions
 */

require_once('Mage/Sales/controllers/OrderController.php');

class Cminds_MultiUserAccounts_Sales_OrderController extends Mage_Sales_OrderController
{
    /**
     * Check order view availability
     *
     * @param   Mage_Sales_Model_Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
            && in_array($order->getState(), $availableStates, $strict = true)
        ) {
            $helper = Mage::helper('cminds_multiuseraccounts');

            if($subAccount = $helper->isSubAccountMode()){
                if (!$helper->canViewAllOrders()) {
                    if($subAccount->getId() != $order->getSubaccountId()){
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }
}
