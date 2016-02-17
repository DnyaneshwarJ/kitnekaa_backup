<?php
/**
 * Quote Controller catches all the action requests from the frontend
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

include_once("Mage/Adminhtml/controllers/Sales/OrderController.php"); 

class Bobcares_Quote2Sales_Adminhtml_QuoteController extends Mage_Adminhtml_Sales_OrderController {
	/*
	 * Main list page
	 */
	public function indexAction(){
        $this->_title($this->__('Quote2Sales'))->_title($this->__('Quotes'));
        $this->_initAction()
        ->renderLayout();
    }
   
    /*
     * Triggers when click on View or on row
     */
   public function viewAction(){
	$this->_initQuote();
   	$this->_initAction();
   	$this->renderLayout();
   }
    
   /*
    * Loads layout with required Breadcrumbs
    */
	protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('quote2sales/quote')
            ->_addBreadcrumb($this->__('Quote2Sales'), $this->__('Quote2Sales'))
            ->_addBreadcrumb($this->__('Quote2Sales'), $this->__('Quotes'));
        return $this;
    }
     /**
     * Extending the original _initOrder to get the quote details instead
     *
     * @return Mage_Sales_Model_Quote || false
     */
   protected function _initQuote()
    {
        $id = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($id);
        $quote_id = $quote->getId(); 
        if (empty($quote_id)) {
            $this->_getSession()->addError($this->__('This quote no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_quote', $quote);
        Mage::register('current_quote', $quote);
        return $quote;
    }
    /**
     * Cancel order
     */
    public function cancelAction() {
        $quote = $this->_initQuote();
        $requestDetails = NULL;

        if ($quote) {
            try {
                $quote->delete()
                        ->save();
                $this->_getSession()->addSuccess(
                        $this->__('The quote has been cancelled.')
                );
                $quoteId = $quote->getId();
                
                //Update status of the request in DB
                $requestModel = Mage::getModel('quote2sales/request');
                $requestIdArray = $requestModel->getQuoteData($quoteId);
                $requestId = $requestIdArray[0]['request_id'];
                
                //If there is a request id then update the status table
                if ($requestId != NULL) {
                    $requestModel->deleteQuoteStatus($quoteId, NULL);
                    $requestDetails = $requestModel->getRequestData($requestId);

                    //If there is no quote/order for the request then update status as "Waiting" in quote2sales_requests table
                    if ($requestDetails == NULL || empty($requestDetails)) {
                        $requestModel->updateRequestStatus("Waiting", $requestId);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The quote has not been cancelled.'));
                Mage::logException($e);
            }
            $this->_redirect('*/*/', array('quote_id' => $quote->getId()));
        } else
            Mage::log("Can't get quote");
    }

    public function massDeleteAction() {
        $orderIds = $this->getRequest()->getPost('quote_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        $requestDetails = NULL;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('quote2sales/quote');
            if ($order->setInactive($orderId)) {
                //    $order->cancel()
                //        ->save();
                $countCancelOrder++;

                //set request id in session
                $requestModel = Mage::getModel('quote2sales/request');
                $requestIdArray = $requestModel->getQuoteData($orderId);
                $requestId = $requestIdArray[0]['request_id'];
                $this->_getSession()->setDelQuoteRequestId((int) $requestId);

                //If there is a request id then update the status table
                if ($requestId != NULL) {

                    //Update status of the request in DB
                    $requestModel->deleteQuoteStatus($orderId, NULL);
                    $requestDetails = $requestModel->getRequestData($requestId);

                    //If there is no quote/order for the request then update status as "Waiting" in quote2sales_requests table
                    if ($requestDetails == NULL || empty($requestDetails)) {
                        $requestModel->updateRequestStatus("Waiting", $requestId);
                    }
                }
            } else {
                $countNonCancelOrder++;
            }
        }
        if ($countNonCancelOrder) {
            if ($countCancelOrder) {
                $this->_getSession()->addError($this->__('%s quote(s) cannot be deleted', $countNonCancelOrder));
            } else {
                $this->_getSession()->addError($this->__('The quote(s) cannot be deleted'));
            }
        }
        if ($countCancelOrder) {
            $this->_getSession()->addSuccess($this->__('%s quote(s) have been deleted.', $countCancelOrder));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export quotes grid to CSV format
     */
    public function exportCsvAction()
    {
    	$fileName   = 'quotes.csv';
    	$grid       = $this->getLayout()->createBlock('quote2sales/adminhtml_quote_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export quotes grid to Excel XML format
     */
    public function exportXmlAction()
    {
    	$fileName   = 'quotes.xml';
    	$grid       = $this->getLayout()->createBlock('quote2sales/adminhtml_quote_grid');
    	$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}