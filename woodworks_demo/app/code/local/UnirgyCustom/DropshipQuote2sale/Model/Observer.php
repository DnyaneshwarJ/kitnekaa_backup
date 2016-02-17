<?php

class UnirgyCustom_DropshipQuote2sale_Model_Observer
{
    public function mapQuoteRequestVendors(Varien_Event_Observer $observer)
    {
        $request_id = $observer->getEvent()->getRequestId();
        $quote_request = $observer->getEvent()->getQuoteRequest();
        $vendors = Mage::app()->getRequest()->getPost('quote_vendors');
        //var_dump(Mage::app()->getRequest()->getPost('quote_vendors'));
        //die();
        try
        {
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn_write = $coreResource->getConnection('core_write');
        $data = array();
        if (count($vendors) > 0) {
            foreach ($vendors as $k => $vendor_id) {
                $data['quote_request_id'] = $request_id;
                $data['vendor_id'] = $vendor_id;
                $conn_write->insert($coreResource->getTableName('dropshipquote2sale/vendorequotes'), $data);
            }
        }
        }
        catch(Exception $e)
        {
        }
    }
    public  function sendQuoteRequestEmailToVendors(Varien_Event_Observer $observer)
    {

        $quote_request = $observer->getEvent()->getQuoteRequest();
        $vendors = Mage::app()->getRequest()->getPost('quote_vendors');

        $bcc_emails=array();
        if (count($vendors) > 0) {
            foreach ($vendors as $k => $vendor_id) {
                $vendor = Mage::helper('udropship')->getVendor($vendor_id);
                $bcc_emails[]=$vendor->getEmail();
            }
            // Bcc Email (Vendors email ids)
            $quote_request->setBccEmails($bcc_emails);
        }
    }
    public function deleteQuoteRequestVendors(Varien_Event_Observer $observer)
    {
        $request_id = $observer->getEvent()->getRequestId();

        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_read');

        $conn->delete(
            $coreResource->getTableName('dropshipquote2sale/vendorequotes'),
            array('quote_request_id= ? '=>$request_id)
        );
    }
}
