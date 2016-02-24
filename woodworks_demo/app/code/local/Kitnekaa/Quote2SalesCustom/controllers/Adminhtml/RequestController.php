<?php
/**
 * Request Controller catches all the requests made by customers
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
include_once("Bobcares/Quote2Sales/controllers/Adminhtml/RequestController.php");
class Kitnekaa_Quote2SalesCustom_Adminhtml_RequestController extends Bobcares_Quote2Sales_Adminhtml_RequestController
{
	public function sendQuoteEmailAction()
	{
		$quoteId=$this->getRequest()->getParam('quote_id');
		$request_id=$this->getRequest()->getParam('request_id');
		$requestTable = Mage::getModel('quote2sales/request')->load($request_id);
		$sellerComment=$requestTable->getSellerComment();
		$quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
		Mage::getModel('quote2sales/email')->sendEmail(new Varien_Object(array('quote'=>$quote)), $sellerComment,$requestTable,true);
		Mage::getSingleton('core/session')->addSuccess($this->__('Checkout link sent successfully!'));
		$this->_redirect('*/adminhtml_request/index');
	}
}