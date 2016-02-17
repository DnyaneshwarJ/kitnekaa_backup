<?php
class Kitnekaa_Credittransfer_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
		public function indexAction()
		{   		
			
		   $financer = $this->getRequest()->getPost('financer');
		   $customer_id =  $this->getRequest()->getPost('customer_id');
		   Mage::helper('credittransfer')->checkExpireDoc($financer,$customer_id);	

           $company_id = Mage::helper('credittransfer')->getpagefield($customer_id,$financer);
	        if(count($company_id)<=0){
	        		$verified = Mage::helper('credittransfer')->isVerifiedDocs($customer_id,$financer);
	        		$verify_count =  count($verified);
	        }
	      echo $this->getLayout()->createBlock('core/template')->assign('data',$company_id)->setTemplate('credittransfer/form/creditform.phtml')->toHtml();       
	           
		}
		protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

         
}

