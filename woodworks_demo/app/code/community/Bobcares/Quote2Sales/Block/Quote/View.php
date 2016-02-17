<?php
class Bobcares_Quote2Sales_Block_Quote_View extends Bobcares_Quote2Sales_Block_Quote_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('quote2sales/view.phtml');
    }

    protected function _prepareLayout()
    {
        //if ($headBlock = $this->getLayout()->getBlock('head')) {
            $this->setTitle($this->__('Quote # %s', $this->getQuoteId()));
        //}
     /*   $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
        */
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }


    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getUrl('*/*/history');
        }
        return Mage::getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('quote2sales')->__('Back to My Quotes');
        }
        return Mage::helper('quote2sales')->__('View Another Quote');
    }

    public function getInvoiceUrl($order)
    {
        return Mage::getUrl('*/*/invoice', array('order_id' => $order->getId()));
    }

    public function getShipmentUrl($order)
    {
        return Mage::getUrl('*/*/shipment', array('order_id' => $order->getId()));
    }

    public function getCreditmemoUrl($order)
    {
        return Mage::getUrl('*/*/creditmemo', array('order_id' => $order->getId()));
    }

}
