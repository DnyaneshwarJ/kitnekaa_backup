<?php

include_once("Mage/Adminhtml/controllers/Sales/Order/CreateController.php");

class Bobcares_Quote2Sales_Adminhtml_Quote_CreateController extends Mage_Adminhtml_Sales_Order_CreateController {

    private $orderData = array();

    protected function _getOrderCreateModel() {
        return Mage::getSingleton('quote2sales/adminhtml_quote_create');
    }

    public function indexAction() {

        $this->_title($this->__('Quote2Sales'))->_title($this->__('Quotes'))->_title($this->__('New Quote'));
        $this->_initSession();
        $this->loadLayout();
        $this->_setActiveMenu('quote2sales/quote')
                ->renderLayout();
    }

    /*
     * Initialize quote creation session data
     */

    protected function _initSession() {
        /**
         * Identify customer
         */
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        /**
         * set request id and corresponding customer id in session
         */
        if ($requestId = $this->getRequest()->getParam('request_id')) {

            //set request id in session
            $this->_getSession()->setRequestId((int) $requestId);

            //Fetch customer id from database using the request id
            $requestModel = Mage::getModel('quote2sales/request');
            $requests = $requestModel->getUserData($requestId);
            $customerId = $requests[0]["customer_id"];

            //set customer id in session
            $this->_getSession()->setCustomerId((int) $customerId);
        }

        /**
         * Identify store
         */
        if ($storeId = $this->getRequest()->getParam('store_id')) {
            $this->_getSession()->setStoreId((int) $storeId);
        }

        /**
         * Identify currency
         */
        if ($currencyId = $this->getRequest()->getParam('currency_id')) {
            $this->_getSession()->setCurrencyId((string) $currencyId);
//          $this->_getOrderCreateModel()->setRecollect(true);
            Mage::getSingleton('quote2sales/adminhtml_quote_create')->setRecollect(true);
        }
        return $this;
    }

    public function loadBlockAction() {
        $request = $this->getRequest();
        Mage::register('rq_quote',$this->getRequest()->getParam('quote_item'));

        $hlp = Mage::helper('udropship/protected');
        $items=$this->_reloadQuote()->_getQuote()->getAllItems();
        //Mage::dispatchEvent('udropship_prepare_quote_items_before', array('items'=>$items));
        $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
        Mage::helper('udropship/item')->initBaseCosts($items);
        $rq=$this->getRequest()->getParam('quote_item');
        try {
            $this->_initSession()
                    ->_processData();
        /*    $quote= $quote = Mage::getModel('sales/quote')->load( $this->_reloadQuote()->_getQuote()->getId());;

            $items=$quote->getAllItems();
            $quote->removeAllItems();
            $quote->save();

            foreach($items as $item){


                if( $rq[$item->getProductId()]['action']!='remove')
                {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    $quote->addProduct($product,
                        new Varien_Object(array('udropship_vendor'=>Mage::helper('udquote2sale')->getVendorId(),'qty'=> $rq[$item->getProductId()]['qty'])));

                }

            }

            $quote->collectTotals();
            $quote->save();*/
        } catch (Mage_Core_Exception $e) {
            $this->_reloadQuote();
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_reloadQuote();
            $this->_getSession()->addException($e, $e->getMessage());
        }


        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('quote2sales_adminhtml_quote_create_load_block_json');
        } else {
            $update->addHandle('quote2sales_adminhtml_quote_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $update->addHandle('quote2sales_adminhtml_quote_create_load_block_' . $block);
            }
        }
        try {
            $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
            $result = $this->getLayout()->getBlock('content')->toHtml();
            if ($request->getParam('as_js_varname')) {
                Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
                $this->_redirect('*/*/showUpdateResult');
            } else {
                $this->getResponse()->setBody($result);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }

    /**
     * Cancel quote create
     */
    public function cancelAction() {
        $this->_getSession()->clear();
        /* $id = $this->getRequest()->getParam('quote_id');
          Mage::log($id);
          $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($id);
          $quote_id = $quote->getId();
          if (empty($quote_id)) {Mage::log("this quote does not exist"); }
         */$this->_redirect('*/adminhtml_quote/index');
    }

    /**
     * Saving quote and create order
     */
    public function saveAction() {
        try {

            $orderIds = $this->getRequest()->getPost('quote_ids', array());
            $sellerComment = $this->getRequest()->getPost('seller_comment');

            /* If request id exists saving the data in table */
            if ($this->_getSession()->getRequestId()) {

                $requestTable = Mage::getModel('quote2sales/request')->getCollection()
                                ->addFieldToFilter('request_id', ((int) $this->_getSession()->getRequestId()))->getFirstItem();
                $requestTable->setData('seller_comment', $sellerComment);
                $requestTable->save();
            }

            $this->_processActionData('save');

            $quote = $this->_getOrderCreateModel()
                    ->setIsValidate(true)
                    ->importPostData($this->getRequest()->getPost('order'))
                    ->saveQuote();
            $quote_id = $this->_getOrderCreateModel()->getQuote()->getId();

            $billing_address = $quote->getBillingAddress()->getData();
            $shipping_address = $quote->getShippingAddress()->getData();

            if ($billing_address['save_in_address_book'])
                $this->setBillingAddress($quote->getBillingAddress()->exportCustomerAddress());
            if ($shipping_address['save_in_address_book'])
                $this->setShippingAddress($quote->getShippingAddress()->exportCustomerAddress());

            /**
             * Added By Shebin on 17/10/2014
             * Send mail on Quote creation
             */
//            calling the Model file Email.php and activating the  send mail Function  that will send the mail.
            Mage::getModel('quote2sales/email')->sendEmail($quote, $sellerComment,$requestTable);


            $customerId = $this->_getSession()->getCustomerId();
            $requestIds = $this->_getSession()->getRequestId();

            //If the request id is null then check in session
            if ($requestIds == NULL) {
                $requestIds = $this->_getSession()->getDelQuoteRequestId();
            }

            //If there is a request id (creating quote based on request) then update the table
            if ($requestIds != NULL) {
                $requestModel = Mage::getModel('quote2sales/request');

                //Update status of the request in quote2sales_requests
                $requestUpdate = $requestModel->updateRequestStatus("Converted To Quote", $requestIds, NULL);
                $requestUpdate = $requestModel->insertQuoteStatus("Converted To Quote", $requestIds, $quote_id);
            }

            /**
             * Additon ends
             */
            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The quote has been created.'));
            $this->_redirect('*/adminhtml_quote/view', array('quote_id' => $quote_id));
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Quote saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    public function setBillingAddress($billing_address) {

        $billingAddressModel = Mage::getModel('customer/address');
        $billingAddressModel->setData($billing_address->getData())
                ->setCustomerId($this->_getSession()->getCustomerId())
                ->setIsDefaultBilling('1')
                ->setSaveInAddressBook('1');

        $billingAddressModel->save();
    }

    public function setShippingAddress($shipping_address) {

        $addressModel = Mage::getModel('customer/address');
        $addressModel->setData($shipping_address->getData())
                ->setCustomerId($this->_getSession()->getCustomerId())
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');

        $addressModel->save();
    }

    public function configureQuoteItemsAction() {

        // Prepare data
        $configureResult = new Varien_Object();
        try {
            $quoteItemId = (int) $this->getRequest()->getParam('id');
            if (!$quoteItemId) {
                Mage::throwException($this->__('Quote item id is not received.'));
            }

            $quoteItem = Mage::getModel('sales/quote_item')->load($quoteItemId);
            if (!$quoteItem->getId()) {
                Mage::throwException($this->__('Quote item is not loaded.'));
            }

            $configureResult->setOk(true);
            $optionCollection = Mage::getModel('sales/quote_item_option')->getCollection()
                    ->addItemFilter(array($quoteItemId));
            $quoteItem->setOptions($optionCollection->getOptionsByItem($quoteItem));

            $configureResult->setBuyRequest($quoteItem->getBuyRequest());
            $configureResult->setCurrentStoreId($quoteItem->getStoreId());
            $configureResult->setProductId($quoteItem->getProductId());
            $sessionQuote = Mage::getSingleton('adminhtml/session_quote');
            $configureResult->setCurrentCustomerId($sessionQuote->getCustomerId());
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        // Render page
        /* @var $helper Mage_Adminhtml_Helper_Catalog_Product_Composite */
        $helper = Mage::helper('adminhtml/catalog_product_composite');
        $helper->renderConfigureResult($this, $configureResult);

        return $this;
    }

    /**
     * Extending parent function
     *
     * @param string $action
     * @return Mage_Adminhtml_Sales_Order_CreateController
     */
    protected function _processActionData($action = null) {
        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request_model' => $this->getRequest(),
            'session' => $this->_getSession(),
        );
        Mage::dispatchEvent('adminhtml_quote2sales_quote_create_process_data_before', $eventData);

        /**
         * Saving order data
         */
        if ($data = $this->getRequest()->getPost('order')) {
            $this->_getOrderCreateModel()->importPostData($data);
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getOrderCreateModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getOrderCreateModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual()) {
            $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
            $shippingMethod = $this->_getOrderCreateModel()->getShippingAddress()->getShippingMethod();
            if (is_null($syncFlag) && $this->_getOrderCreateModel()->getShippingAddress()->getSameAsBilling() && empty($shippingMethod)
            ) {
                $this->_getOrderCreateModel()->setShippingAsBilling(1);
            } else {
                $this->_getOrderCreateModel()->setShippingAsBilling((int) $syncFlag);
            }
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')) {
            $this->_getOrderCreateModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getOrderCreateModel()->getQuote()->isVirtual() &&
                $this->getRequest()->getPost('collect_shipping_rates')
        ) {
            $this->_getOrderCreateModel()->collectShippingRates();
        }


        /**
         * Apply mass changes from sidebar
         */
        if ($data = $this->getRequest()->getPost('sidebar')) {
            $this->_getOrderCreateModel()->applySidebarData($data);
        }

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int) $this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPost());
        }

        $items = array();
        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int) $this->getRequest()->getPost('remove_item');
        $removeFrom = (string) $this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
        }

        /**
         * Move quote item
         */
        $moveItemId = (int) $this->getRequest()->getPost('move_item');
        $moveTo = (string) $this->getRequest()->getPost('to');
        if ($moveItemId && $moveTo) {
            $this->_getOrderCreateModel()->moveQuoteItem($moveItemId, $moveTo);
        }

        /* if ($paymentData = $this->getRequest()->getPost('payment')) {
          $this->_getOrderCreateModel()->setPaymentData($paymentData);
          } */


        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        $eventData = array(
            'order_create_model' => $this->_getOrderCreateModel(),
            'request' => $this->getRequest()->getPost(),
        );

        Mage::dispatchEvent('adminhtml_quote2sales_quote_create_process_data', $eventData);

        $this->_getOrderCreateModel()
                ->saveQuote();

        // Make the quote active
        $this->_getOrderCreateModel()->getQuote()->setData("is_active", 1);


        if ($paymentData = $this->getRequest()->getPost('payment')) {
            $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
        }

        /**
         * Saving of giftmessages
         */
        $giftmessages = $this->getRequest()->getPost('giftmessage');
        if ($giftmessages) {
            $this->_getGiftmessageSaveModel()->setGiftmessages($giftmessages)
                    ->saveAllInQuote();
        }

        /**
         * Importing gift message allow items from specific product grid
         */
        if ($data = $this->getRequest()->getPost('add_products')) {
            $this->_getGiftmessageSaveModel()
                    ->importAllowQuoteItemsFromProducts(Mage::helper('core')->jsonDecode($data));
        }

        /**
         * Importing gift message allow items on update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', array());
            $this->_getGiftmessageSaveModel()->importAllowQuoteItemsFromItems($items);
        }



        $data = $this->getRequest()->getPost('order');
        $couponCode = '';
        if (isset($data) && isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }
        if (!empty($couponCode)) {
            if ($this->_getQuote()->getCouponCode() !== $couponCode) {
                $this->_getSession()->addError(
                        $this->__('"%s" coupon code is not valid.', $this->_getHelper()->escapeHtml($couponCode)));
            } else {
                $this->_getSession()->addSuccess($this->__('The coupon code has been accepted.'));
            }
        }
        return $this;
    }

    /**
     * Saves the Quote into the customer's cart and proceeds to checkout
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
        $userEmail = $customer->getEmail();

        $transaction = Mage::getModel('core/resource_transaction');
        $storeId = $customer->getStoreId();
        $reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $order = Mage::getModel('sales/order')
                ->setIncrementId($reservedOrderId)
                ->setStoreId($storeId)
                ->setQuoteId($quoteId)
                ->setGlobal_currency_code($currencyCode)
                ->setBase_currency_code($currencyCode)
                ->setStore_currency_code($currencyCode)
                ->setOrder_currency_code($currencyCode);

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
            $orderItem = Mage::getModel('sales/order_item')
                    ->setStoreId($storeId)
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

    /**
     * Extending the original _initOrder to get the quote details instead
     * @return Mage_Sales_Model_Quote || false
     */
    protected function _initQuote() {
        $id = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($id);
        $quoteId = $quote->getId();

        //If there is no quote id then display an error message
        if (empty($quoteId)) {
            $this->_getSession()->addError($this->__('This quote no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_quote', $quote);
        Mage::register('current_quote', $quote);
        return $quote;
    }

}
