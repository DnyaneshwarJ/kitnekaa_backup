<?php

class Kitnekaa_Quote2SalesCustom_Block_Request_Request extends Bobcares_Quote2Sales_Block_Quote_Abstract{

    protected $request_id;
    public function __construct()
    {
        parent::__construct();
        $this->request_id= $this->getRequest()->getParam('request_id');
        $requests = Mage::getModel('quote2sales/request')->getCollection()
            ->addFieldToFilter('request_id', ((int)$this->request_id));
        $this->setQuoteRequest($requests);
    }

    protected function getRequestId()
    {
        return $this->request_id;
    }

    protected function getBackUrl()
    {
        return Mage::getUrl('quote2sales/request/index');
    }
}
