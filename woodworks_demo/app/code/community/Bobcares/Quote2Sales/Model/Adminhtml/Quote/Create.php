<?php
class Bobcares_Quote2Sales_Model_Adminhtml_Quote_Create extends Mage_Adminhtml_Model_Sales_Order_Create 
{
	    /**
	     * Initialize creation data from existing quote
	     *
	     * @param Mage_Sales_Model_Quote $quote
	     * @return unknown
	     */

	    protected function _initBillingAddressFromQuote(Mage_Sales_Model_Quote $order)
	    {
	    	$this->getQuote()->getBillingAddress()->setCustomerAddressId('');
	    	Mage::helper('core')->copyFieldset(
	    	'sales_copy_order_billing_address',
	    	'to_order',
	    	$order->getBillingAddress(),
	    	$this->getQuote()->getBillingAddress()
	    	);
	    }
	    
	    protected function _initShippingAddressFromQuote(Mage_Sales_Model_Quote $order)
	    {
	    	$orderShippingAddress = $order->getShippingAddress();
	    	$quoteShippingAddress = $this->getQuote()->getShippingAddress()
	    	->setCustomerAddressId('')
	    	->setSameAsBilling($orderShippingAddress && $orderShippingAddress->getSameAsBilling());
	    	Mage::helper('core')->copyFieldset(
	    	'sales_copy_order_shipping_address',
	    	'to_order',
	    	$orderShippingAddress,
	    	$quoteShippingAddress
	    	);
	    }
	    /**
	     * Initialize creation data from existing order Item
	     *
	     * @param Mage_Sales_Model_Quote_Item $orderItem
	     * @param int $qty
	     * @return Mage_Sales_Model_Quote_Item | string
	     */

}