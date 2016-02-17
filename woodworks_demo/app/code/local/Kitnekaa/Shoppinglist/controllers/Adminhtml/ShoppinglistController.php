<?php

 
class Kitnekaa_Shoppinglist_Adminhtml_ShoppinglistController extends Mage_Adminhtml_Controller_Action
{
      public function indexAction(){


            
         }
         public function editAction() //shows item list in current shoplist
         {

               if($this->getRequest()->isXmlHttpRequest())
               {
                    $this->loadLayout();
                    $this->getResponse()->setBody(
                    $this->getLayout()->createBlock('shoppinglist/adminhtml_editlist_grid')->toHtml()); 
           
                }else{
                        $this->loadLayout();
                        $this->renderLayout();
                      }

         }

         public function additemAction()  //shows grid for adding item  to current shoplist
         {

          
           if($this->getRequest()->isXmlHttpRequest())
               {
                    $this->loadLayout();
                    $this->getResponse()->setBody(
                    $this->getLayout()->createBlock('shoppinglist/adminhtml_allproductlist_grid')->toHtml()); 
           
                }else{
                        $this->loadLayout();
                        $this->renderLayout();
                      }

         }

         public function saveitemAction() //saves added item to shoplist
         {
                $product_id = $this->getRequest()->getPost('productid', array());
                $list_id =  $this->getRequest()->getParam('list_id');
                $customer_id = $this->getRequest()->getParam('customer_id');
                $shoppinglist_model =  Mage::getModel('shoppinglist/shoppinglist');
                $shoppinglist_core_model =  Mage::getModel('shoppinglist/shoppinglist');
                $list_data =array();
              foreach ($product_id as $product) {
              $current_product = Mage::getModel('catalog/product')->load($product);
              $shoppinglist_model =  Mage::getModel('shoppinglist/shoppinglistitems');
              //$product_model = Mage::getModel('credittransfer/')->load($doc);
              $product_data = array();
              $product_data['item_name'] = $current_product->getName();
              $product_data['sku'] = $current_product->getSku();
              $product_data['product_id'] = $current_product->getEntityId();
              $product_data['price'] = $current_product->getPrice();
              $product_data['list_id'] = $list_id;
              $product_data['added_by'] = $customer_id;
              $shoppinglist_model->setData($product_data)->save();
              
              }

              $customer_data = Mage::getModel('customer/customer')->load($customer_id);
              $name = $customer_data->getFirstname().' '.$customer_data->getLastname();
              $list_data['list_id'] = $list_id;
              $list_data['updated_by']= $name;
              $list_data['update_time'] = date('Y-m-d H:i:s');
              $shoppinglist_core_model->setData($list_data)->save();
              Mage::getSingleton('adminhtml/session')->addSuccess('Selected Products Added Successfully');
              $this->_redirect('*/shoppinglist/edit/', array('list_id' => $list_id,'customer_id' => $customer_id )); 
        
         }

         public function addshoplistAction() //shows form for adding shoplist
         {
              //echo "in adding shoplist";
                $customerid =  $this->getRequest()->getParam('customer_id');
             
                        $this->loadLayout();
                        $this->renderLayout();
         }

          public function saveshoplistAction() //save form of shoplist
         {
             
              $shoppinglist_model =  Mage::getModel('shoppinglist/shoppinglist');
              $customer_data = Mage::getModel('customer/customer')->load($_POST['customer_id']);
              $name = $customer_data->getFirstname().' '.$customer_data->getLastname();
         
           
              $list_data = array();
              $list_data['list_name']= $_POST['title'];
              $list_data['company_id']= $_POST['company'];
              $list_data['created_by']= $name;
              $list_data['created_time'] = date('Y-m-d H:i:s');
          
              $shoppinglist_model->setData($list_data)->save();
              Mage::getSingleton('adminhtml/session')->addSuccess('Shopping list Added Successfully');
              $this->_redirect('*/customer/edit/', array('id' => $_POST['customer_id'], 'active_tab' => 'edit_customer_shoppinglist')); 

         }

         public function  msitemdeleteAction() //delete item in shoppinglist
         {    
             $list_id = $this->getRequest()->getParam('list_id');
             $customer_id = $this->getRequest()->getParam('customer_id');
             $item_id = $this->getRequest()->getPost('item_id', array());
             
            foreach ($item_id as $item) 
              {
                  
                  $current_item =  Mage::getModel('shoppinglist/shoppinglistitems')->load($item);
                  $current_item->delete();
              
              }
              Mage::getSingleton('adminhtml/session')->addSuccess('Selected Products Removed Successfully');
            $this->_redirect('*/shoppinglist/edit/', array('customer_id' => $customer_id, 'list_id' => $list_id)); 
            
         }

         public function deleteshoplistAction()
         {

          echo $customer_id = $this->getRequest()->getParam('customer_id');
          $list_id = $this->getRequest()->getPost('list_id', array());
          foreach ($list_id as $list) 
              {
                  
                  $current_list =  Mage::getModel('shoppinglist/shoppinglist')->load($list);
                  $list_item = Mage::getModel('shoppinglist/shoppinglistitems')->getCollection();
                  $list_item->addFieldToFilter('list_id',array('eq'=> $list));
                  
                  if(count($list_item)<=0){
                      $current_list->delete();
                      echo"Delete->".$current_list->getListName();
            Mage::getSingleton('core/session')->addSuccess('List Name->'.$current_list->getListName().' Deleted Successfully');
                  }
                  else{
                    Mage::getSingleton('core/session')->addError('Failed To Delete->'.$current_list->getListName().' . first delete all product of this list
                  ');
                  }
                    
              
              }
          
          $this->_redirect('*/customer/edit/', array('id' => $customer_id, 'active_tab' => 'edit_customer_shoppinglist')); 

         }

         public function shownonexistformAction()
         {
            $this->loadLayout();
            $this->renderLayout();
         }

         public function savenonexistprodAction()
         { 
           $shoppinglist_model =  Mage::getModel('shoppinglist/shoppinglistitems');
           $prod = array();
           $productid = $this->getRequest()->getParam('product_id');
           $customer_id = $this->getRequest()->getParam('customer_id');
           $prod['list_id'] = $this->getRequest()->getParam('list_id');
           $prod['sku'] = $this->getRequest()->getParam('sku');
           $prod['item_name'] = $this->getRequest()->getParam('prodname');
            if(isset($productid))
              {  $prod['id'] = $productid;   }
           $prod['description'] = $this->getRequest()->getParam('description');
           $shoppinglist_model->setData($prod)->save();
           $last_item_id = $shoppinglist_model->getId(); 
 

                $uploaded_images = array();
                $images_count = count($_FILES['prodfile']['name']);
                $uploaded_images_count = 0;
                $path = Mage::helper('shoppinglist')->getAttachmentUploadPath();
                if(!is_dir($path)) {
                    mkdir($path);
                }
          if(isset($_FILES['prodfile']['name']))
          {
             foreach ($_FILES['prodfile']['name'] as $key => $value) 
              {
                try{ 
                      $uploader = new Varien_File_Uploader(
                            array(
                                    'name' => $_FILES['prodfile']['name'][$key],
                                    'type' => $_FILES['prodfile']['type'][$key],
                                    'tmp_name' => $_FILES['prodfile']['tmp_name'][$key],
                                    'error' => $_FILES['prodfile']['error'][$key],
                                    'size' => $_FILES['prodfile']['size'][$key]
                                  )
                        );
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $uploaded = $uploader->save($path, $_FILES['prodfile']['name'][$key]);
                           
                            if($uploaded['error'] == 0){
                                $uploaded_images_count = $uploaded_images_count + 1;
                                $uploaded_images[$key] = $_FILES['prodfile']['name'][$key]." has been successfully uploaded";
                            }
                            //$data['prodfile'] = $_FILES['images']['name'][$key];
                             $shopp_list_files = Mage::getModel('shoppinglist/shoppinglistfiles');
                             $shopp_list_files->setListItemId($last_item_id);
                            $shopp_list_files->setFileName($uploader->getUploadedFileName());
                            $shopp_list_files->save();



                     }catch(Exception $e){

                      Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                     }       

             }
          }   

           if($images_count == $uploaded_images_count):
                    Mage::getSingleton('adminhtml/session')->addSuccess("Total ".$uploaded_images_count." images uploded out of ".$images_count." Images has been uploaded");
                    foreach ($uploaded_images as $uploaded_image)
                    {
                    Mage::getSingleton('adminhtml/session')->addSuccess($uploaded_image);
                    }
                    
                else:
                    Mage::getSingleton('adminhtml/session')->addError("Total ".$uploaded_images_count." images uploded out of ".$images_count." Images has been uploaded");
                    foreach ($uploaded_images as $uploaded_image){
                        Mage::getSingleton('adminhtml/session')->addError($uploaded_image);
                    }
                endif;

                $this->_redirect('*/shoppinglist/edit/', array('customer_id' => $customer_id, 'list_id' => $prod['list_id'])); 

         }  

        public function deleteitemfileAction()
  {

          $path = Mage::helper('shoppinglist')->getAttachmentUploadPath();
          try{
          $customer_id =  $this->getRequest()->getParam('customer_id');
          $list_id     = $this->getRequest()->getParam('list_id');
          $item_id     =  $this->getRequest()->getParam('itemid');
          $product_id  =  $this->getRequest()->getParam('product_id');
          $current_item =  Mage::getModel('shoppinglist/shoppinglistfiles')->load($item_id);
          unlink($path. DS .$current_item->getFileName());
          $current_item->delete();

          Mage::getSingleton('core/session')->addSuccess('File Deleted Successfully');
          
          $this->_redirect('*/shoppinglist/shownonexistform/', array('customer_id' => $customer_id, 'list_id' => $list_id, 'product_id'=> $product_id)); 
        }catch(Exception $e){

          Mage::getSingleton('core/session')->addError('Fail to Delete with error'.$e);
        }
  }

} 