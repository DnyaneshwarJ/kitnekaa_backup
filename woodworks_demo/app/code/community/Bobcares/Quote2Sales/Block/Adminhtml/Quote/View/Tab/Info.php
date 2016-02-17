<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Tab_Info
    extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Abstract
    //extends Mage_Adminhtml_Block_Widget 
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::registry('current_quote');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return array(
            'can_display_total_due'      => true,
            'can_display_total_paid'     => true,
            'can_display_total_refunded' => true,
        );
    }

    public function getQuoteInfoData()
    {
        return array(
            'no_use_order_link' => true,
        );
    }

    public function getTrackingHtml()
    {
        return $this->getChildHtml('order_tracking');
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * Retrieve giftmessage block html
     *
     * @deprecated after 1.4.2.0, use self::getGiftOptionsHtml() instead
     * @return string
     */
    public function getGiftmessageHtml()
    {
        return $this->getChildHtml('order_giftmessage');
    }

    /**
     * Retrieve gift options container block html
     *
     * @return string
     */
    public function getGiftOptionsHtml()
    {
        return $this->getChildHtml('gift_options');
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('*/*/*', array('order_id'=>$orderId));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('quote2sales')->__('Information');
    }

    public function getTabTitle()
    {
        return Mage::helper('quote2sales')->__('Quote Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
