<?php
class UnirgyCustom_DropshipQuote2sale_Model_Email extends Bobcares_Quote2Sales_Model_Email {

    public function sendEmail(Bobcares_Quote2Sales_Model_Adminhtml_Quote_Create $quote, $sellerComment,$quote_request) {

//        Variables or data Extaracted from the quote object about the quote for using in the template email.
        $quoteObj = $quote->getQuote();

//        $subject = "Quote generated #". $quoteObj->getId();

        $templateId = Mage::getStoreConfig('quotes/email/email_quote_template');

        $sender = Mage::getStoreConfig('quotes/email/sender_email_identity');

        $customer = $quoteObj->getCustomerFirstname() . " " . $quoteObj->getCustomerLastname();

        $customerEmail = $quoteObj->getCustomerEmail();

        $createdAt = Mage::helper('core')->formatDate($quoteObj->getCreatedAt(), 'medium', false);

        $currency_code = $quoteObj->getQuoteCurrencyCode();

        $currency_symbol = Mage::app()->getLocale()->currency($currency_code)->getSymbol(); //toget the currency symbol from the Currency code.

        $subTotal = $quoteObj->getSubtotal();

        $grandTotal = $quoteObj->getBaseGrandTotal();

        $discount = $quoteObj->getShippingAddress()->getBaseDiscountAmount();

        $subtotalDiscount = $quoteObj->getSubtotalWithDiscount();

        $shippingTitle = $quoteObj->getShippingAddress()->getShippingDescription();

        $shippingAmount = $quoteObj->getShippingAddress()->getBaseShippingAmount();
        $websiteName = $quoteObj->getStore()->getWebsite()->getName();

//        Get all Quote Items
        $items = $quote->getQuote()->getAllItems();
        $quoteDetails = null;

//        for each Item in the Quote Items
        foreach ($items as $item) {

//            Quote table rows as string
            $quoteDetails .= '<tr><td align="left" width="325"  style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;"><strong>' . $item['name'] . '<br>sku: </strong>' . $item['sku']
                    . '</td><td align="right" width="325"  style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;">' . Mage::helper('core')->currency($item['price'], true, false)
                    . '</td><td align="right" width="325"  style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;">' . $item['qty']
                    . '</td><td align="right" width="325"  style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;">' . Mage::helper('core')->currency($item['row_total'], true, false) . '</td></tr>';
        }

//        Quote totals as a table in string
        $quote_footer = '<table>';

        $quote_footer .= '<tr><td align="right" width="975" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >SubTotal : </td><td>&nbsp;</td><td align="right" width="325" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >' . Mage::helper('core')->currency($subTotal, true, false) . '</td></tr>';

        if ($discount) {
            $quote_footer .= '<tr><td align="right" width="975" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >Discount : </td><td>&nbsp;</td><td align="right" width="325" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >' . Mage::helper('core')->currency($discount, true, false) . '</td></tr>';
        }

        if (!$quoteObj->isVirtual()) { // if the product is not virtual
            $quote_footer .= '<tr><td align="right" width="975" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >Shipping & Delivery ( ' . $shippingTitle . ' ) : </td><td>&nbsp;</td><td align="right" width="325" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" >' . Mage::helper('core')->currency($shippingAmount, true, false) . '</td></tr>';
        }

        $quote_footer .= '<tr><td align="right" width="975" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" ><strong>GrandTotal : </strong></td><td>&nbsp;</td><td align="right" width="325" style="font-size:13px;padding:5px 9px 6px 9px; line-height:1em;" ><strong>' . Mage::helper('core')->currency($grandTotal, true, false) . '</strong></td></tr>';

        $quote_footer .= '</table>';

//      Parametes the template email variable to send
        $params = array(
            'customer' => $customer,
            'quoteid' => $quote->getQuote()->getId(),
            'createdAt' => $createdAt,
            'currency_code' => $currency_code,
            'currency_symbol' => $currency_symbol,
            'subTotal' => $subTotal,
            'subTotalDiscount' => $subtotalDiscount,
            'discount' => $discount,
            'shippingTitle' => $shippingTitle,
            'shippingAmount' => $shippingAmount,
            'grandTotal' => $grandTotal,
            'website' => $websiteName,
            'quoteDetails' => $quoteDetails,
            'quote_footer' => $quote_footer,
            'sellerComment' => $sellerComment
        );


        $params['request_type']=$quote_request->getRequestType()=='Service'?true:false;

        $params['vendor_id']=false;
        if (Mage::helper('udquote2sale')->getVendorId())
        {
            $vendor = Mage::helper('udropship')->getVendor(Mage::helper('udquote2sale')->getVendorId());
            $params['vendor_name']=$vendor->getVendorName();
            $logo = $vendor->getLogo() ? Mage::helper('udropship')->getResizedVendorLogoUrl($vendor, 60, 60) : '';
            $params['vendor_logo']=$logo;
            $params['vendor_id']=Mage::helper('udquote2sale')->getVendorId();
        }

        $email_cc=explode(',',Mage::getStoreConfig('quotes/email/quote_email_cc'));
        $current_cc_email=explode(',',Mage::app()->getRequest()->getPost('cc_quote_emails'));

        $all_cc_email=array_filter(array_merge($email_cc,$current_cc_email));
        $translate = Mage::getSingleton('core/translate');
        $email_obj=Mage::getModel('core/email_template');
        if(count($all_cc_email)>0)
        {
            $email_obj ->addBcc($all_cc_email);
        }
        $email_obj ->sendTransactional($templateId, $sender, $customerEmail, $customer, $params, Mage::app()->getStore()->getId());
        $translate->setTranslateInline(true);
    }

}
