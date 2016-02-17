<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Abstract extends Mage_Adminhtml_Block_Widget
{
    /**
     * Retrieve available quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
    	  if (Mage::registry('current_quote')) {
            return Mage::registry('current_quote');
        }
	   Mage::throwException(Mage::helper('quote2sales')->__('Cannot get quote instance'));
    }
    public function getPriceDataObject()
    {
        echo "q";die;
        $obj = $this->getData('price_data_object');
        if (is_null($obj)) {
            return $this->getOrder();
        }
        return $obj;
    }

    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {

        return $this->helper('adminhtml/sales')->displayPriceAttribute($this->getPriceDataObject(), $code, $strong, $separator);
    }

    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br/>')
    {
        return $this->helper('adminhtml/sales')->displayPrices($this->getPriceDataObject(), $basePrice, $price, $strong, $separator);
    }

    /**
     * Retrieve order info block settings
     *
     * @return array
     */
    public function getQuoteInfoData()
    {
        return array();
    }
    

    /**
     * Retrieve subtotal price include tax html formated content
     *
     * @param Varien_Object $item
     * @return string
     */
    public function displayShippingPriceInclTax($order)
    {
        $shipping = $order->getShippingInclTax();
        if ($shipping) {
            $baseShipping = $order->getBaseShippingInclTax();
        } else {
            $shipping       = $order->getShippingAmount()+$order->getShippingTaxAmount();
            $baseShipping   = $order->getBaseShippingAmount()+$order->getBaseShippingTaxAmount();
        }
        return $this->displayPrices($baseShipping, $shipping, false, ' ');
    }
}
