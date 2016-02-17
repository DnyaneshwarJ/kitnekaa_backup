<?php

/**
 * Block Functions needed by the Checkout link section of the View template
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Quote_Checkout extends Mage_Checkout_Block_Onepage_Link {

    public function getQuote() {
        return $this->getParentBlock()->getQuote();
    }

    public function getCheckoutUrl() {
        $quote_id = $this->getQuote()->getId();
        return $this->getUrl('*/*/checkout', array('quote_id' => $quote_id));
    }

}
