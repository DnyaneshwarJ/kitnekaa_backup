<?php
  
class Kitnekaa_Credittransfer_Helper_Data extends Mage_Core_Helper_Abstract
{
  
          
		public  function findcompany()
		{

			$customer = Mage::registry('current_customer');
			$customer_id = $customer->getId();
        
        return $customer_id;
    	}

    	public function findcustomer($doc_id){
    		$select = Mage::getModel('credittransfer/docs')->getCollection();
    		$select->addFieldToSelect(array('customer_id'));
    		$select->addFieldToFilter('main_table.id',array('eq'=> $doc_id));
    		$data = $select->getData();
    		return $data;
    	}

      public function financername($financer)
      {


      	$financer_model = Mage::getModel('credittransfer/verifyingcompany')->getCollection();
                        $financer_model->addFieldToSelect('verifying_company_name');
                        $financer_model->addFieldToFilter('verifying_company_id',array('eq'=> $financer));
       $financer_name = $financer_model->getData();   
       return $financer_name; 

      }
    	public function getpagefield($customer_id,$financer)
    	{

			     $select = Mage::getModel('credittransfer/docneeded')->getCollection();
		         $mod  = Mage::getSingleton('core/resource')->getTableName('credittransfer/docname');
		         $mod2 = Mage::getSingleton('core/resource')->getTableName('credittransfer/docs');  
		         $select->addFieldToSelect(array('doc_id','verifying_company_id'));
		         $select->getSelect()->joinLeft(array('SecondTable'=>$mod),'main_table.doc_id=SecondTable.doc_id', array('SecondTable.doc_name','SecondTable.has_time_period'));
		         $select->getSelect()->joinLeft(array('ThirdTable'=>$mod2),'main_table.doc_id=ThirdTable.doc_id and main_table.verifying_company_id=ThirdTable.verifying_company_id and ThirdTable.actives = 1 and ThirdTable.customer_id="'.$customer_id.'"', 
		         array('ThirdTable.doc_path','ThirdTable.from_date','ThirdTable.to_date','ThirdTable.verified','ThirdTable.actives','ThirdTable.customer_id'));
		         $select->addFieldToFilter('main_table.verifying_company_id',array('eq'=> $financer));
		         $select->addFieldToFilter('ThirdTable.doc_path',array('null'=> true));
		         $d = $select->getData();
		         //echo $d = $select->getSelect();
		         return $d;

    	}

     public function isVerifiedDocs($customer_id,$financer){

             $select = Mage::getModel('credittransfer/docneeded')->getCollection();
             $mod  = Mage::getSingleton('core/resource')->getTableName('credittransfer/docname');
             $mod2 = Mage::getSingleton('core/resource')->getTableName('credittransfer/docs');  
             $select->addFieldToSelect(array('doc_id','verifying_company_id'));
             $select->getSelect()->joinLeft(array('SecondTable'=>$mod),'main_table.doc_id=SecondTable.doc_id', array('SecondTable.doc_name','SecondTable.has_time_period'));
             $select->getSelect()->joinLeft(array('ThirdTable'=>$mod2),'main_table.doc_id=ThirdTable.doc_id and main_table.verifying_company_id=ThirdTable.verifying_company_id and ThirdTable.customer_id="'.$customer_id.'"', 
             array('ThirdTable.doc_path','ThirdTable.from_date','ThirdTable.to_date','ThirdTable.verified','ThirdTable.actives','ThirdTable.customer_id'));
             $select->addFieldToFilter('main_table.verifying_company_id',array('eq'=> $financer));
             $select->addFieldToFilter('ThirdTable.verified',array('eq'=> 0));
             $isverified = $select->getData();
             //$d = $select->getSelect();
             return $isverified;
        
     }


    public function getFinancerList(){
                $financer_model = Mage::getModel('credittransfer/verifyingcompany')->getCollection();
                $financer_name = $financer_model->getData();   
                return $financer_name; 

    }

    public function checkExpireDoc($financer,$customer_id){
        $date =  date('d-m-Y');
        $doc_model = Mage::getModel('credittransfer/docs')->getCollection();

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $table = $resource->getTableName('credittransfer/docs');
        $active = 0;
        $query = "UPDATE {$table} SET actives = '{$active}' WHERE to_date < '"
                 .$date."'";
        $writeConnection->query($query);
        

    }
   

   }