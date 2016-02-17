<?php

class UnirgyCustom_DropshipQuote2sale_Model_Request extends Kitnekaa_Quote2SalesCustom_Model_Request {
   public function load($_request_id)
   {
       $collection = Mage::getModel('quote2sales/request')->getCollection();
       if ($this->getVendorId()) {
           $collection->getSelect()->joinLeft('vendor_quotes', 'main_table.request_id=vendor_quotes.quote_request_id', array('vendor_id'));
           $collection->addFieldToFilter('vendor_id', array('eq' => $this->getVendorId()));
           $collection->addFieldToFilter('request_id', array('eq' => $_request_id));

           if (is_null($collection->getFirstItem()->getRequestId())) {
               return parent::load(0);
           }
       }
           return parent::load($_request_id);

   }

    protected function getVendorId()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        return (!is_null($admin->getVendorId())) ? $admin->getVendorId() : false;
    }
}
