<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Request_View_Info extends Mage_Adminhtml_Block_Widget
{
    /**
     * Retrieve required options from parent
     */
/*    protected function _beforeToHtml()
    {
    	parent::_beforeToHtml();
		$this->setOrder($this->getRequest());
        foreach ($this->getQuoteInfoData() as $k => $v) {
            $this->setDataUsingMethod($k, $v);
        }
    }
  */  public function getRequest()
    {
    	return Mage::registry('current_request');
    }
    
    public function getCustomer(){
    	return Mage::registry('current_customer');
    }
    
    public function getCustomerGroupName()
    {
        if ($this->getRequest()) {
        	
            return Mage::getModel('customer/group')->load($this->getCustomer()->getGroupId())->getCode();
        }
        return null;
    }

	 public function getCustomerName()
    {
    	$customer = $this->getCustomer();
        if ($customer->getName()) {
            $customerName = $customer->getName();
        }
        else {
            $customerName = Mage::helper('quote2sales')->__('Guest');
        }
        
        return $customerName;
    }
    public function getCustomerEmail()
    {
    	$customer = $this->getCustomer();
    	if ($customer->getEmail()) {
    		$customerEmail = $customer->getEmail();
    	}
    	else {
    		$customerEmail = "Email is not set";
    	}
    
    	return $customerEmail;
    }
    public function getCustomerViewUrl()
    { 
        if (!$this->getRequest()->getCustomer_id()){
	    	return false; 
	    }
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getRequest()->getCustomer_id()));
    }

    public function getViewUrl($request_id)
    {
        return $this->getUrl('*/adminhtml_request/view', array('id'=>$request_id));
    }

    public function getDeliveryLocation($id)
    {
        $address=Mage::getModel('customer/address')->load($id);
        return $address->format('oneline');
    }
}
