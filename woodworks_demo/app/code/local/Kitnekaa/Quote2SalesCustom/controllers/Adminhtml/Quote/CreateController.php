<?php

include_once("Bobcares/Quote2Sales/controllers/Adminhtml/Quote/CreateController.php");

class Kitnekaa_Quote2SalesCustom_Adminhtml_Quote_CreateController extends Bobcares_Quote2Sales_Adminhtml_Quote_CreateController
{
    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        $update_items     = $this->getRequest()->getPost('item');
        $seq_update_items = NULL;
        foreach ($update_items as $ui) {
            $seq_update_items[] = $ui;
        }

        $quote = $this->_getSession()->getQuote();
        $items = $quote->getAllItems();

        $i = 0;
        foreach ($items as $item) {
            $item->setPrice($seq_update_items[$i]['custom_price']);
            $item->setCustomPrice($seq_update_items[$i]['custom_price']);
            $item->setOriginalCustomPrice($seq_update_items[$i]['custom_price']);
            $item->setQty($seq_update_items[$i]['qty']);
            $item->getProduct()->setIsSuperMode(TRUE);
            $item->save();
            $i++;
        }

        $quote->save();
        $quote->setTotalsCollectedFlag(FALSE)->collectTotals();

        try {

            $orderIds      = $this->getRequest()->getPost('quote_ids', array());
            $sellerComment = $this->getRequest()->getPost('seller_comment');

            /* If request id exists saving the data in table */
            if ($this->_getSession()->getRequestId()) {

                $requestTable = Mage::getModel('quote2sales/request')->getCollection()
                                    ->addFieldToFilter('request_id', ((int)$this->_getSession()->getRequestId()))->getFirstItem();
                $requestTable->setData('seller_comment', $sellerComment);
                $requestTable->save();
            }

            $this->_processActionData('save');

            $quote    = $this->_getOrderCreateModel()
                             ->setIsValidate(TRUE)
                             ->importPostData($this->getRequest()->getPost('order'))
                             ->saveQuote();
            $quote_id = $this->_getOrderCreateModel()->getQuote()->getId();

            $billing_address  = $quote->getBillingAddress()->getData();
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

            Mage::getModel('quote2sales/email')->sendEmail($quote, $sellerComment);


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
        }
        catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Quote saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
}
