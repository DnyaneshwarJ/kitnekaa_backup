<?php
class Kitnekaa_Shoppinglist_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getAttachmentUploadPath()
    {
        return  Mage::getBaseDir() . DS . 'media' . DS . 'shopfiles' . DS;
    }

    public  function getAttachmentUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'shopfiles/';
    }

    public function getAddressContactNo($id)
    {
        $address=Mage::getModel('customer/address')->load($id);
        return $address->getTelephone();
    }

    // Admin Side functions

    public function getShoppinglistname($listid)
    {
             $listname = Mage::getModel('customer/address')->load($id);
        return $address->getTelephone();

    }

    public function findcompany($customer_id=0){

            if($customer_id==0){ $customer = Mage::registry('current_customer');
            $customer_id = $customer->getCompanyId();
            }
            else{

                $customer = Mage::getModel('users/company')->getCollection()->addFieldToFilter('customer_id', array('eq'=> $customer_id))->getFirstItem();
                //print_r($customer->getData());die();
            $customer_id = $customer->getCompanyId();

       
            }
        return $customer_id;
    }
   
    public function findshoplistname($listid)
    {  
            $list = Mage::getModel('shoppinglist/shoppinglist')->load($listid);
            return $list->getListName();
         
    }

}
	 