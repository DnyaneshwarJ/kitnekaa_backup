<?php
include_once("Bobcares/Quote2Sales/controllers/Adminhtml/Quote/EditController.php");
class Kitnekaa_Quote2SalesCustom_Adminhtml_Quote_EditController extends Bobcares_Quote2Sales_Adminhtml_Quote_EditController
{
    /**
     * Saves the Quote into the customer's cart and proceeds to checkout
     * Added Dispatch events
     */
    public function quoteToOrderAction() {
        require_once 'app/Mage.php';
        Mage::app();
        $quoteId = $this->getRequest()->getParam('quote_id');

        //Fetch customer id
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
        $id = $quote->customer_id;
        $itemsCount = $quote->items_count;
        $customer = Mage::getModel('customer/customer')->load($id);
        $company=Mage::getModel('users/company')->load($customer->getCompanyId());
        $userEmail = $customer->getEmail();

        $transaction = Mage::getModel('core/resource_transaction');
        $storeId = $customer->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $order = Mage::getModel('sales/order');
        Mage::dispatchEvent('set_quote_to_order',array('order'=>$order,'quote'=>$quote));
        $order ->setIncrementId($reservedOrderId)
            ->setStoreId($storeId)
            ->setQuoteId($quoteId)
            ->setGlobal_currency_code($currencyCode)
            ->setBase_currency_code($currencyCode)
            ->setStore_currency_code($currencyCode)
            ->setOrder_currency_code($currencyCode)
            ->setCompanyId($company->getCompanyId())
            ->setCompanyName($company->getCompanyName());

        // set Customer data
        $order->setCustomer_email($customer->getEmail())
            ->setCustomerFirstname($customer->getFirstname())
            ->setCustomerLastname($customer->getLastname())
            ->setCustomerGroupId($customer->getGroupId())
            ->setCustomer_is_guest(0)
            ->setCustomer($customer);
        $billing = $customer->getDefaultBillingAddress();

        //If there is no default billing addess then fetch the same from quote
        if ($billing == "") {
            $billing = $quote->getBillingAddress()->exportCustomerAddress();
        }

        $shipping = $customer->getDefaultShippingAddress();

        //If there is no default shipping addess then fetch the same from quote
        if ($shipping == "") {
            $shipping = $quote->getShippingAddress()->exportCustomerAddress();
        }

        $billingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setCustomerId($customer->getId())
            ->setCustomerAddressId($customer->getDefaultBilling())
            ->setCustomer_address_id($billing->getEntityId())
            ->setPrefix($billing->getPrefix())
            ->setFirstname($billing->getFirstname())
            ->setMiddlename($billing->getMiddlename())
            ->setLastname($billing->getLastname())
            ->setSuffix($billing->getSuffix())
            ->setCompany($billing->getCompany())
            ->setStreet($billing->getStreet())
            ->setCity($billing->getCity())
            ->setCountry_id($billing->getCountryId())
            ->setRegion($billing->getRegion())
            ->setRegion_id($billing->getRegionId())
            ->setPostcode($billing->getPostcode())
            ->setTelephone($billing->getTelephone())
            ->setFax($billing->getFax());
        $order->setBillingAddress($billingAddress);

        //If there is no shipping address then set the same as billing address
        //Else set the shipping address
        if ($shipping == "") {
            $shippingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($customer->getId())
                ->setCustomerAddressId($customer->getDefaultShipping())
                ->setCustomer_address_id($billing->getEntityId())
                ->setPrefix($billing->getPrefix())
                ->setFirstname($billing->getFirstname())
                ->setMiddlename($billing->getMiddlename())
                ->setLastname($billing->getLastname())
                ->setSuffix($billing->getSuffix())
                ->setCompany($billing->getCompany())
                ->setStreet($billing->getStreet())
                ->setCity($billing->getCity())
                ->setCountry_id($billing->getCountryId())
                ->setRegion($billing->getRegion())
                ->setRegion_id($billing->getRegionId())
                ->setPostcode($billing->getPostcode())
                ->setTelephone($billing->getTelephone())
                ->setFax($billing->getFax());
        } else {
            $shippingAddress = Mage::getModel('sales/order_address')
                ->setStoreId($storeId)
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($customer->getId())
                ->setCustomerAddressId($customer->getDefaultShipping())
                ->setCustomer_address_id($shipping->getEntityId())
                ->setPrefix($shipping->getPrefix())
                ->setFirstname($shipping->getFirstname())
                ->setMiddlename($shipping->getMiddlename())
                ->setLastname($shipping->getLastname())
                ->setSuffix($shipping->getSuffix())
                ->setCompany($shipping->getCompany())
                ->setStreet($shipping->getStreet())
                ->setCity($shipping->getCity())
                ->setCountry_id($shipping->getCountryId())
                ->setRegion($shipping->getRegion())
                ->setRegion_id($shipping->getRegionId())
                ->setPostcode($shipping->getPostcode())
                ->setTelephone($shipping->getTelephone())
                ->setFax($shipping->getFax());
        }

        $shippingMethode = $quote->getShippingAddress()->getShippingMethod();
        $shippingDescription = $quote->getShippingAddress()->getShippingDescription();
        $order->setShippingAddress($shippingAddress)
            ->setShipping_method($shippingMethode)
            ->setShippingDescription($shippingDescription);

        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($storeId)
            ->setCustomerPaymentId(0)
            ->setMethod('purchaseorder')
            ->setPo_number(' - ');
        $order->setPayment($orderPayment);
        $subTotal = 0;
        $orderData = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);

        //get all items
        $itemsData = $orderData->getAllItems();

        //loop for all items in product
        foreach ($itemsData as $itemIds => $itemValue) {
            $products[$itemValue->getProductId()] = array('qty' => $itemValue->getQty());
            $rowTotal = $itemValue->getPrice() * $itemValue->getQty();
            $options =$itemValue->getProduct()->getTypeInstance(true)->getOrderOptions($itemValue->getProduct());
            $options=$options['options'];
            $item_options=null;
            foreach($options as $option)
            {
                $item_options['additional_options'][]=array('label'=>$option['label'],'value'=>$option['value'],
                    'print_value' => $option['value']);
            }
            //var_dump($options);die;
            $orderItem = Mage::getModel('sales/order_item');
            Mage::dispatchEvent('set_quote_item_to_order_item',array('order'=>$order,'order_item'=>$orderItem,'quote'=>$quote,'quote_item'=>$itemValue));
                $orderItem->setStoreId($storeId)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(NULL)
                ->setProductId($itemValue->getProductId())
                ->setProductType($itemValue->getTypeId())
                ->setQtyBackordered(NULL)
                ->setTotalQtyOrdered($itemValue->getRqty())
                ->setQtyOrdered($itemValue->getQty())
                ->setName($itemValue->getName())
                ->setSku($itemValue->getSku())
                ->setPrice($itemValue->getPrice())
                ->setBasePrice($itemValue->getPrice())
                ->setOriginalPrice($itemValue->getPrice())
                ->setRowTotal($rowTotal)
                ->setBaseRowTotal($rowTotal);
            if(!is_null($item_options)){
                $orderItem ->setProductOptions($item_options);
            }
            $subTotal += $rowTotal;
            $order->addItem($orderItem);
        }

        $shippingAmount = $quote->getShippingAddress()->getShippingAmount();
        $subTotal = $quote->getSubtotal();
        $baseTotal = $quote->getBaseSubtotal();
        $grandTotal = $quote->getGrandTotal();
        $baseGrandTotal = $quote->getBaseGrandTotal();
        $order->setSubtotal($subTotal)
            ->setBaseSubtotal($baseTotal)
            ->setGrandTotal($grandTotal)
            ->setBaseGrandTotal($baseGrandTotal);

        $order->setShippingAmount($shippingAmount);
        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));
        $transaction->save();
        $quote = $this->_initQuote();

        $orderId = $order->getEntityId();

        //Update status of the request in DB
        $requestModel = Mage::getModel('quote2sales/request');
        $requestIdArray = $requestModel->getQuoteData($quoteId);
        $requestId = $requestIdArray[0]['request_id'];

        /**
         * Version : Quote2Sales 0.8.3
         * Fixed the issue in converting a quote gerenated by user to an order in admin panel
         */
        //If there is a request id then update the status of that request
        if ($requestId != NULL) {
            $requestModel->updateRequestStatus("Converted To Order", $requestId);
            $requestModel->addOrderId("Converted To Order", $quoteId, $orderId);
        }

        // initialize the core mailer model and mail info model
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');

        // addTo($mailaddress) specifies the receiver(s) of the mail
        $emailInfo->addTo($userEmail);
        $mailer->addEmailInfo($emailInfo);
        $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);

        // the template params are used in the template file via {{var order.customer_name}}
        $mailer->setTemplateParams(
            array(
                'order' => $order,
                'billing' => $order->getBillingAddress(),
                'payment_html' => ""
            )
        );

        // send out the mail
        $mailer->send();

        //if there ia quote then delete it else display an error message
        if ($quote) {
            try {
                $quote->delete()
                    ->save();
            } catch (Mage_Core_Exception $e) {

            } catch (Exception $e) {
                Mage::logException($e);
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The Order has been created.'));
            $this->_redirect('*/adminhtml_quote/index');
        } else {
            Mage::log("Can't get quote");
        }
    }
}
