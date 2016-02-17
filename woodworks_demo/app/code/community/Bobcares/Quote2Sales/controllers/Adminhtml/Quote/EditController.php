<?php
require_once('CreateController.php');
class Bobcares_Quote2Sales_Adminhtml_Quote_EditController extends Bobcares_Quote2Sales_Adminhtml_Quote_CreateController
{
	public function editAction(){
		$started = $this->startAction(0);

		if ($started){
			$quoteId = $this->getRequest()->getParam('quote_id');
			//$quote = Mage::getModel('sales/quote')->load($quoteId);
			$quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);  
                        
			$quote->delete()
			->save();
			$this->_redirect('*/adminhtml_quote_create/index');
		}
		
	}
	public function duplicateAction(){
		$started = $this->startAction(1);
	
		if ($started){
			$this->_redirect('*/adminhtml_quote_create/index');
		}
	
	}
        
    /**
     * Start quote initialization
     */
    public function startAction($identifier) {
        $this->_getSession()->clear();
        $requestDetails = NULL;
        $quoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
        try {
            if ($quote->getId()) {

                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromQuote($quote);

                //set request id in session
                $requestModel = Mage::getModel('quote2sales/request');
                $requestIdArray = $requestModel->getQuoteData($quoteId);
                $requestId = $requestIdArray[0]['request_id'];
                $this->_getSession()->setDelQuoteRequestId((int) $requestId);
                
                //If the function is calling from editAction then delete entry from the DB
                if ($identifier == 0) {

                    //If there is a request id then update the status table
                    if ($requestId != NULL) {

                        //Delete quote entry from the DB
                        $requestModel->deleteQuoteStatus($quoteId, NULL);
                        $requestDetails = $requestModel->getRequestData($requestId);

                        //If there is no quote/order for the request then update status as "Waiting" in quote2sales_requests table
                        if ($requestDetails == NULL || empty($requestDetails)) {
                            $requestModel->updateRequestStatus("Waiting", $requestId);
                        }
                    }
                }
                return true;
            } else {
                $this->_redirect('*/adminhtml_quote/');
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/adminhtml_quote/view', array('quote_id' => $quoteId));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException($e, $e->getMessage());
            $this->_redirect('*/adminhtml_quote/view', array('quote_id' => $quoteId));
        }
    }

    /**
     * Index page
     */
    public function indexAction()
    {
        $this->_title($this->__('Quote2Sales'))->_title($this->__('Quotes'))->_title($this->__('Edit Quote'));
        $this->loadLayout();

        $this->_initSession()
            ->_setActiveMenu('quote2sales/quotes')
            ->renderLayout();
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('quote2sales/quote/actions/edit');
    }
}
