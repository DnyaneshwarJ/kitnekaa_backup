<?php
class Kitnekaa_Credittransfer_AjaxController extends Mage_Core_Controller_Front_Action
{
		public function indexAction()
		{   
			$financer = $this->getRequest()->getPost('financer');
			$customer= $this->_getSession();
        	$customer= Mage::getModel('customer/customer')->load($customer->getId());
            $parent_customer=Mage::helper('users')->getParentCustomerData($customer);
            $customer_id = $parent_customer->getId();
            $company_id=Mage::helper('credittransfer')->getpagefield($customer_id,$financer);
	        
	        echo $this->getLayout()->createBlock('core/template')->assign('data',$company_id)->setTemplate('credittransfer/form/creditform.phtml')->toHtml();       
	          // echo $this->getLayout()->createBlock('core/template')->assign('data',$company_id)->setTemplate('credittransfer/form/creditform.phtml')->toHtml();       
		}
		protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

         
}

