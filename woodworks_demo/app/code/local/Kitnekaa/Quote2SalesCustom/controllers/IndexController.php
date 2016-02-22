<?php
include_once("Bobcares/Quote2Sales/controllers/IndexController.php");

class Kitnekaa_Quote2SalesCustom_IndexController extends Bobcares_Quote2Sales_IndexController {

    /**
     * @desc Rejects current quote and redirects to RFQ page
     * @param $quoteId quote Id to reject
     */
    public function acceptQuoteAction() {

        /* If request has quote Id */
        if ($_GET['acceptquoteid']) {
            $quoteId=$_GET['acceptquoteid'];
        }
        $requestId = Mage::getModel('quote2sales/requeststatus')->getCollection()
                        ->addFieldToSelect('request_id')
                        ->addFieldToFilter('quote_id', ((int) $quoteId))->getFirstItem()->getData('request_id');

        /* If request Id exists */
        if ($requestId) {
            $statusTable = Mage::getModel('quote2sales/requeststatus')->getCollection()
                            ->addFieldToFilter('quote_id', ((int) $quoteId))->getFirstItem();
            $statusTable->setData('status', 'Quote Accepted');
            $statusTable->save();
            $requestTable = Mage::getModel('quote2sales/request')->getCollection()
                            ->addFieldToFilter('request_id', ((int) $requestId))->getFirstItem();
            $requestTable->setData('status', 'Quote Accepted');
            $requestTable->save();
        }
        Mage::getSingleton('core/session')->addSuccess($this->__('Quote Accepted Successfully!'));
        $this->_redirect('*/*/acceptedQuote');

    }

    public function acceptedQuoteAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
