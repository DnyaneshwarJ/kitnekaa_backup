    <?php

    class Kitnekaa_Credittransfer_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract 
    {
      protected $_canUseInternal = true;
      protected $_canUseCheckout = true;
      protected $_code  = 'credittransfer';
      protected $_formBlockType = 'credittransfer/form_credittransfer';
      protected $_infoBlockType = 'credittransfer/info_credittransfer';
      
    
     protected function _getSession()
        {
            return Mage::getSingleton('customer/session');
        }

      public function assignData($data)
      {  //var_dump($data);
        //die();
          $customer=$this->_getSession();
          $customer= Mage::getModel('customer/customer')->load($customer->getId());
  
          $info = $this->getInfoInstance();
          $parent_customer=Mage::helper('users')->getParentCustomerData($customer);
          $customer_id = $parent_customer->getId();

          if(!isset($customer_id)){

                  $customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
                  $customer_id =  $customer->getEntityid(); 
                 

          }
          $financer = $_POST['financer'];
          $info->setAdditionalInformation('financer', $financer);
          $financer_name = Mage::helper('credittransfer')->financername($financer);    
          $financer_name = $financer_name[0];
      
          $j = Mage::helper('credittransfer')->getpagefield($customer_id,$financer);
          $upload_docs_model = Mage::getModel('credittransfer/docs');

              foreach ($j as $value) 
              {
                 $filename = $value['doc_name'];
                               
                if(isset($_FILES[$filename]['name'])) 
                {
                    
                    $uploaded_images_count = 0; 
                    $path = Mage::getBaseDir('media') . DS .'company'. DS .'documents'. DS .$customer_id. DS .$financer_name['verifying_company_name']. DS;
                    if(!is_dir($path))
                    {
                        mkdir($path);
                    }
                   
                    $doc_data = array();
                    $doc_data['customer_id'] = $customer_id;
                    $doc_data['verifying_company_id'] = $financer;
                    $doc_data['doc_id'] = $value['doc_id'];
                    if($value['has_time_period']==1)
                      {
                        $doc_data['from_date'] = $_POST[$filename.'_from_date'];
                        $doc_data['to_date']   = $_POST[$filename.'_to_date'];
                      }
                        try{
                        
                            $uploader = new Varien_File_Uploader(
                                array(
                                    'name' => $_FILES[$filename]['name'],
                                    'type' => $_FILES[$filename]['type'],
                                    'tmp_name' => $_FILES[$filename]['tmp_name'],
                                    'error' => $_FILES[$filename]['error'],
                                    'size' => $_FILES[$filename]['size']
                                )
                            );
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf')); // or pdf or anything
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            $uploader->save($path, $_FILES[$filename]['name']);
                            $filename = $uploader->getUploadedFileName();

                            if(!empty($filename)){
                                $uploaded_images_count++;
                            }
                             
                             $doc_data['doc_path'] = $filename;
                             $upload_docs_model->setData($doc_data)->save();
                           
                        }catch(Exception $e) {
                          //Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                          Mage::log("custom error");
                          
                        } 
                   
                }


          
              }
               
       
        

    Mage::dispatchEvent('customer_save_after', $arrayName = array('customer' =>$customer,'editcompany'=>1));


       /* if ($data->getVatDocs())
        {
          $info->setVatDocs($data->getvatDocs());
        }
         
        if ($data->getBankDocs())
          {
          $info->setBankDocs($data->getBankDocs());
        }  */
        
        //  if ($data->getCustBranchName())
        // {
        //   $info->setCustBranchName($data->getCustBranchName());
        // }


        //  if ($data->getCustTransDate())
        // {
        //   $info->setCustTransDate($data->getCustTransDate());
        // }
     
        return $this;
      }
     
      public function validate()
      {
        parent::validate();
        $info = $this->getInfoInstance();
         
        // if (!$info->getCustUtrNumber())
        // {
        //   $errorCode = 'invalid_data';
        //   $errorMsg = $this->_getHelper()->__("CustomFieldOne is a required field.\n");
        // }
         
        // if (!$info->getCustBankName())
        // {
        //   $errorCode = 'invalid_data';
        //   $errorMsg .= $this->_getHelper()->__('CustomFieldTwo is a required field.');
        // }
     
        // if ($errorMsg) 
        // {
        //   Mage::throwException($errorMsg);
        // }
     
        return $this;
      }

    }