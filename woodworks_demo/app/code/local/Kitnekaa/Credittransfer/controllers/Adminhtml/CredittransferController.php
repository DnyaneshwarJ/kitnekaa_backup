<?php


class Kitnekaa_Credittransfer_Adminhtml_CredittransferController extends Mage_Adminhtml_Controller_Action
{
    
    
    public function addmyblockAction()  //adding grid on click of get all 
        {   
            
            $companydata = $this->getRequest()->getPost();
             Mage::register('financer', $companydata['financer']);
             Mage::register('company', $companydata['company']);
             Mage::register('verifyingname', $companydata['verifyname']);

            $block = Mage::app()->getLayout()->createBlock('credittransfer/adminhtml_customerverification_mytab')->assign('data',array('myval'=>"value"))->setTemplate('credittransfer/customerverification/mytab.phtml');
           
            echo $block->toHtml();
          }   


    
    
    public function deleteAction()   // delete files on delete
    {
         
        $docId =  $this->getRequest()->getParam('id');
        $file = Mage::getModel('credittransfer/docs')->load($docId);
        $financer =  $this->getRequest()->getParam('financer');
         $customer_id =  $this->getRequest()->getParam('customer_id');
        $subAccount = Mage::getSingleton('credittransfer/docs')->load($docId)->delete();
        unlink(Mage::getBaseDir('media') . DS .'company'. DS .'documents'. DS .$customer_id. DS .$financer. DS .$file->getDocPath()); 
       // $message = $this->__('Document Deleted Successfully');
       //  Mage::getSingleton('adminhtml/session')->addSuccess($message);

    }

        public function msdeleteAction()   // mass action method for setting files as verified
    {
        
         $docid = $this->getRequest()->getPost('docid', array());
        
         $customer_id = Mage::helper('credittransfer')->findcustomer($docid[0]); 
        
         $customer_id = $customer_id[0];
         $temp = $customer_id['customer_id'];
           foreach ($docid as $doc) {
                 $file = Mage::getModel('credittransfer/docs')->load($doc);
                 $file->setData('verified','1')->save();
                 $file->setData('under_verification','0')->save();
                
                 
           }
        $this->_redirect('*/customer/edit/', array('id' => $temp , 'active_tab' => 'credit_verification')); 
      }  
    
 
    public function sendverificationmailAction()   /* send mail to trade financer with folder link*/   
     {

        $customer_id = $this->getRequest()->getParam('customer_id');
       $url = Mage::helper('adminhtml')->getUrl('*/customer/edit/', array('_secure' => true, 'id' => $customer_id , 'active_tab' => 'credit_verification'));
        // $emailTemplate  = Mage::getModel('core/email_template')
        //                         ->loadDefault('custom_email_template1');                                    
         
        // //Create an array of variables to assign to template
        // $emailTemplateVariables = array();
        // $emailTemplateVariables['myvar1'] = 'Branko';
        // $emailTemplateVariables['myvar2'] = 'Ajzele';
        // $emailTemplateVariables['myvar3'] = 'ActiveCodeline';
         
        // $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
         
        // $mail = Mage::getModel('core/email')
        // ->setToName('sachin')
        // ->setToEmail('sachinndevkar@gmail.com')
        // ->setBody($processedTemplate)
        // ->setSubject('Subject :')
        // ->setFromEmail('sachinndevkar@gmail.com')
        // ->setFromName('Raaj')
        // ->setType('html');
        // try{
        //     //Confimation E-Mail Send
        //     $mail->send();
        // }
        // catch(Exception $error)
        // {
        //     Mage::getSingleton('core/session')->addError($error->getMessage());
        //     return false;
        // }

        $emailTemplate  = Mage::getModel('core/email_template')
                                ->loadDefault('credit_verification_email');     

         
        //Create an array of variables to assign to template
        $emailTemplateVariables = array();
        $emailTemplateVariables['myvar1'] = 'Hello';
        $emailTemplateVariables['myvar2'] = 'Seller';
        $emailTemplateVariables['myvar3'] = $url;
         
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
         //var_dump($emailTemplate);  
        // die();
        $mail = Mage::getModel('core/email')
        ->setToName('sachin')
        ->setToEmail('sachinndevkar@gmail.com')
        ->setBody($processedTemplate)
        ->setSubject('Subject :')
        ->setFromEmail('kitnekaa@gmail.com')
        ->setFromName('Kitnekaa Admin Team')
        ->setType('html');
       //echo $mail;

        try{
            //Confimation E-Mail Send
            $mail->send();
        }
        catch(Exception $error)
        {
            Mage::getSingleton('core/session')->addError($error->getMessage());
            return false;
        }
        
         $this->_redirect('*/customer/edit/', array('id' => $customer_id, 'active_tab' => 'credit_verification'));


    }

    public  function adddocsAction()
    {
      $financer = $this->getRequest()->getParam('financer');
      $customer_id = $this->getRequest()->getParam('customer_id');
      $verifyer = $this->getRequest()->getParam('verifyingname');
      
      $this->loadLayout()->_addBreadcrumb('Upload File');
      $this->renderLayout();
                    
    }

    public function addfileAction(){
        $doc_data = array();
        $financer = $_POST['financer'];
        $customer_id = $_POST['customer_id'];
        $verifyingname = $_POST['verifyingname'];
        $docs  = explode("_", $_POST['doc_id']);
       
        $doc_id = $docs[0];
        $timeperiod = $docs[1];
        $filename = $_FILES['docfile']['name'];
        $upload_docs_model = Mage::getModel('credittransfer/docs');
           if($timeperiod > 0)
                      { 

                        $doc_data['from_date'] = $_POST['from_date'];
                        $doc_data['to_date'] = $_POST['to_date'];
                      }
                                    
                  if(isset($_FILES['docfile']['name']))
                {            
                  
                    $path = Mage::getBaseDir('media') . DS .'company'. DS .'documents'. DS .$customer_id. DS .$verifyingname. DS;
                    if(!is_dir($path))
                    {
                        mkdir($path);
                    }
                    $uploader = new Varien_File_Uploader(
                                array(
                                    'name' => $_FILES['docfile']['name'],
                                    'type' => $_FILES['docfile']['type'],
                                    'tmp_name' => $_FILES['docfile']['tmp_name'],
                                    'error' => $_FILES['docfile']['error'],
                                    'size' => $_FILES['docfile']['size']
                                )
                            );
                            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf')); // or pdf or anything
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            $uploader->save($path, $_FILES['docfile']['name']);
                            $filename = $uploader->getUploadedFileName();
                                                        
                            $doc_data['customer_id'] = $customer_id;
                            $doc_data['verifying_company_id'] = $financer;
                            $doc_data['doc_id'] = $doc_id;
                            $doc_data['doc_path'] = $filename;
                            $upload_docs_model->setData($doc_data)->save();
                            


              }else{

                echo"No File Selected";
              }

               $this->_redirect('*/customer/edit/', array('id' => $customer_id, 'active_tab' => 'credit_verification'));
   }



}