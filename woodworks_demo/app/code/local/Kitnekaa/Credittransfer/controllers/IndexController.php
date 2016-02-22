<?php
class Kitnekaa_Credittransfer_IndexController extends Mage_Core_Controller_Front_Action
{
		public function indexAction()
		{   
			echo "hiii";
		 $select = Mage::getModel('credittransfer/verifyingcompany')->getCollection();
         $mod  = Mage::getSingleton('core/resource')->getTableName('credittransfer/docneeded');
         $mod2 = Mage::getSingleton('core/resource')->getTableName('credittransfer/docs');   
        //we changed mysql query, we added inner join to order item table
        // $collection->join('credittransfer/docsneeded', 'ord_id=entity_id', array('name'=>'name', 'sku' =>'sku', 'qty_ordered'=>'qty_ordered' ), null,'left');
        // $collection->join(array('payment'=>'sales/order_payment'),'main_table.entity_id=parent_id','method');
        // $this->setCollection($collection);
        
            $customer=$this->_getSession();
            $customer= Mage::getModel('customer/customer')->load($customer->getId());
           

             $parent_customer=Mage::helper('users')->getParentCustomerData($customer);
             $customer_id = $parent_customer->getId();
             $company_id=Mage::helper('users')->getCustomerAttributeValue($parent_customer,'company_id');

             $select->getSelect()->joinLeft(array('b'=>$mod),'main_table.verifying_company_id=b.verifying_company_id', array('b.doc_id'));
             $select->getSelect()->joinLeft(array('c'=>$mod2),'b.verifying_company_id = c.verifying_company_id AND b.doc_id = c.doc_id AND c.under_verification = 1 and c.company_id='.$company_id, 
             array('c.doc_path','c.company_id','c.under_verification'));
            $select->getSelect()->group('main_table.verifying_company_id');
            echo $select->getSelect();
		}

		public function savedataAction()
		{   
			$event_data_array = $this->getRequest()->getPost();
			//Mage::dispatchEvent('customer_save_after', $event_data_array);
		}	
		    protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

        public function addmyblockAction()
        {   
            $companydata = $this->getRequest()->getPost();
            //Mage::dispatchEvent('customer_save_after', $event_data_array);
        }   

}