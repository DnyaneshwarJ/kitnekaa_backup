<?php
/**
 * Request Controller catches all the requests made by customers
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

class Bobcares_Quote2Sales_Adminhtml_RequestController extends Mage_Adminhtml_Controller_action //Bobcares_Quote2Sales_Adminhtml_QuoteController
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('quote2sales/request')
			->_addBreadcrumb(Mage::helper('quote2sales')->__('Requests'), Mage::helper('quote2sales')->__('Requests'));

		return $this;
	}
	/*
	 * Loads the Requests admin index page
	*/
	public function indexAction() {
		$this->_initAction();
		$this->renderLayout();
	}
	/*
	 * Display the view page
	*/
	public function viewAction(){
		$request = $this->_initRequest();
		$this->_initCustomer($request);

		$this->_initAction();
//		$this->_addContent($this->getLayout()->createBlock('quote2sales/adminhtml_request_view'));
//		->_addLeft($this->getLayout()->createBlock('clientdb/adminhtml_server_edit_tabs'));

		$this->renderLayout();
	}

	/**
	 * Getting request details
	 *
	 * @return Mage_Sales_Model_Quote || false
	 */
	protected function _initRequest()
	{
		$id = $this->getRequest()->getParam('id');
		$request = Mage::getModel('quote2sales/request')->load($id);
		$request_id = $request->getId();
		if (empty($request_id)) {
			$this->_getSession()->addError($this->__('This request no longer exists.'));
			$this->_redirect('*/*/');
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
			return false;
		}
		Mage::register('current_request', $request);
		return $request;
	}

	protected function _initCustomer($request){
		$customerId = $request->getCustomer_id();
		if ($customerId) {
			$customer = Mage::getModel('customer/customer')->load($customerId);
			Mage::register('current_customer', $customer);

			return $customer;
		} else return false;
	}

	public function massDeleteAction() {
		$requestIds = $this->getRequest()->getParam('quote2sales');

		if(!is_array($requestIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('quote2sales')->__('Please select request(s)'));
		} else {
			try {
				$count = 0;
				foreach ($requestIds as $id) {
					$request = Mage::getModel('quote2sales/request')->load($id);
					$request->delete();

					//Delete quote entry from the DB
					$requestModel = Mage::getModel('quote2sales/request');
					$requestModel->deleteQuoteStatus(NULL, $id);
					$count++;
				}
				if ($count >= count($requestIds))
					Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('quote2sales')->__(
							'Total of %d request(s) were successfully deleted', $count
						)
					);
				else Mage::getSingleton('adminhtml/session')->addError(Mage::helper('quote2sales')->__('%d requests cannot be deleted.', count($requestIds)-$count));
			} Catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * Export requests grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'requests.csv';
		$grid       = $this->getLayout()->createBlock('quote2sales/adminhtml_request_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	/**
	 *  Export requests grid to Excel XML format
	 */
	public function exportXmlAction()
	{
		$fileName   = 'requests.xml';
		$grid       = $this->getLayout()->createBlock('quote2sales/adminhtml_request_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

	/** Allow to customer group*/
	protected function _isAllowed(){
		return true;
	}

}