<?php

class Company_Users_Model_Observer extends Mage_Core_Model_Abstract
{

    /* Occur before customer saving */
    public function customer_save_before($observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $parent_customer=Mage::helper('users')->getParentCustomerData($customer);
        $company_name=Mage::app()->getRequest()->getPost('company');
        $company_type=Mage::app()->getRequest()->getPost('company_type');

        if(Mage::helper('users')->isEmailExistInSubaccount($parent_customer))
        {
            Mage::getSingleton('core/session')->addError('This customer email already exists!');
            goto error;
        }elseif(Mage::helper('users')->isCompanyExist($parent_customer,$company_name,$company_type))
        {
            $session = Mage::getSingleton('customer/session');
            $session->setCustomerFormData(Mage::app()->getRequest()->getPost());
            Mage::getSingleton('core/session')->addError('Company Already exist!');
            goto error;
        }
        else
        {
            goto success;
        }

        error:
        {
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
            Mage::app()->getResponse()->sendResponse();
            exit;
        }

        success:
        {
            return $this;
        }
    }


    /* Occur after customer saving */
    public function customer_save_after($observer)
    {

        $event = $observer->getEvent();
        $customer = $event->getCustomer();

        /* Saving company id against user */
        $company_name=Mage::app()->getRequest()->getPost('company');
        $parent_customer=Mage::helper('users')->getParentCustomerData($customer);
        $vat_no=Mage::app()->getRequest()->getPost('vat_no');
        $tin_no=Mage::app()->getRequest()->getPost('tin_no');
        $company_type=Mage::app()->getRequest()->getPost('company_type');
        $company_vat_tin_verified = Mage::app()->getRequest()->getPost('vat_tin_verified');
        $vat_tin_verified_by = Mage::app()->getRequest()->getPost('vat_tin_verified_by');
        $canSendMail = 'no';

        $customer_id = $parent_customer->getId();
        $edit_customer_account=Mage::app()->getRequest()->getPost('edit_customer_account');
        $company_id=Mage::helper('users')->getCustomerAttributeValue($parent_customer,'company_id');

        if($data = Mage::app()->getRequest()->getPost())
        {
            if(isset($_FILES['upload_docs']['name'])) {
                $uploaded_images_count = 0;
                $path = Mage::getBaseDir('media') . DS .'company'. DS .'documents'. DS .$customer_id. DS;
                if(!is_dir($path)) {
                    mkdir($path);
                }

                $upload_docs_model = Mage::getModel('uploaddocs/companydocs');
                $doc_data = array();
                $doc_data['company_id'] = $company_id;
                $doc_data['customer_id'] = $customer_id;
                foreach($_FILES['upload_docs']['name'] as $key => $image){
                    try {
                        $uploader = new Varien_File_Uploader(
                            array(
                                'name' => $_FILES['upload_docs']['name'][$key],
                                'type' => $_FILES['upload_docs']['type'][$key],
                                'tmp_name' => $_FILES['upload_docs']['tmp_name'][$key],
                                'error' => $_FILES['upload_docs']['error'][$key],
                                'size' => $_FILES['upload_docs']['size'][$key]
                            )
                        );
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf')); // or pdf or anything
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $uploader->save($path, $_FILES['upload_docs']['name'][$key]);
                        $filename = $uploader->getUploadedFileName();

                        if(!empty($filename)){
                            $uploaded_images_count++;
                        }

                        $doc_data['file_name'] = $filename;
                        $doc_data['type'] = $_FILES['upload_docs']['type'][$key];
                        $upload_docs_model->setData($doc_data)->save();

                    }catch(Exception $e) {
                        //Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    }
                }

            }
        }

        $company=Mage::getModel('users/company')->load($company_id);
        $old_vat_no = $company->getVatNo();
        $old_tin_no = $company->getTinNo();

        if($uploaded_images_count || $old_vat_no != $vat_no || $old_tin_no != $tin_no){
            $canSendMail = 'yes';
        }

        if($canSendMail == 'yes'){
            $to_emailaddress = Mage::getStoreConfig('companyusers_sections/companyusersvattinverification_group/vattinverification_to');
            $cc_emailaddress = Mage::getStoreConfig('companyusers_sections/companyusersvattinverification_group/vattinverification_cc');
            $senderName = Mage::getStoreConfig('companyusers_sections/companyusersvattinverification_group/vattinverification_name');
            $fromName = Mage::getStoreConfig('trans_email/ident_general/name');
            $fromEmail = Mage::getStoreConfig('trans_email/ident_general/email');

            $to_emailaddress_array = array_filter(explode(',',$to_emailaddress));
            $cc_emailaddress_array = explode(',',$cc_emailaddress);

            $body = "Hi ".'<br/>';
            $body .= "Customer with name ".$parent_customer->getFirstname().' '.$parent_customer->getLastname()." has updated his company vat or tin no or uploaded company docs".'<br/>';
            $body .= "Please verify the same";

            //$to_emailaddress_array = array_merge($to_emailaddress_array,$cc_emailaddress_array);

            if(count($to_emailaddress_array)>0)
            {
                $mail = Mage::getModel('core/email')
                    ->setToName($senderName)
                    ->setToEmail($to_emailaddress_array)
                    //->addBcc($cc_emailaddress_array)
                    ->setBody($body)
                    ->setSubject('New Notification on Company Information Update')
                    ->setFromEmail($fromEmail)
                    ->setFromName($fromName)
                    ->setType('html')
                    ->send();
            }

        }

        if($company_type!=1 || empty($company_type)){$company_name=Mage::helper('users')->getBuyerTypeLabel(0);}

        if(is_null($company_id) || count($company->getData())==0 || empty($company_id))
        {
            if(!empty($company_name)) {
                $company = Mage::getModel('users/company');
                $company->setCompanyName($company_name);
                $company->setVatNo($vat_no);
                $company->setTinNo($tin_no);
                $company->setCustomerId($customer_id);
                if (!empty($company_vat_tin_verified)) {
                    $company->setVatTinVerified($company_vat_tin_verified);
                }
                if(isset($company_type)){
                    $company->setCompanyType($company_type);
                }
                $company_id = $company->save()->getCompanyId();
                $parent_customer->setCompanyId($company_id);
                $parent_customer->getResource()->saveAttribute($customer, 'company_id');
            }
        }
        else
        {

            if($edit_customer_account)
            {
                $company->setCompanyName($company_name);
                $company->setVatNo($vat_no);
                $company->setTinNo($tin_no);
                $company->setCustomerId($customer_id);
                if(isset($company_type)){
                    $company->setCompanyType($company_type);
                }
                if(isset($company_vat_tin_verified)){
                    $company->setVatTinVerified($company_vat_tin_verified);
                }
                if(!empty($vat_tin_verified_by) && isset($company_vat_tin_verified)){
                    $company->setVatTinVerifiedBy($vat_tin_verified_by);
                }
            }

            $saved_vat_tin_no = $company->getVatTinVerified();
            if(!$saved_vat_tin_no){
                $company->setVatTinVerifiedBy('');
            }

            $company->save();
        }

        Mage::getSingleton('customer/session')->setCompany($company);
        return $this;
    }

    public  function set_company_in_session($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $company=Mage::helper('users')->getCompany($customer->getCompanyId());
        Mage::getSingleton('customer/session')->setCompany($company);
    }


    /**
    * checks wether vat and tin number is added by customer if not shows notice message to the customer to add the same.
    * @author Pradeep Sanku
    */
    public function ask_customer_for_vatandtinno($observer){
       // $is_ajax=Mage::app()->getRequest()->getPost('is_ajax');
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $company_model = Mage::getModel('users/company')->load(Mage::getSingleton('customer/session')->getCustomer()->getCompanyId());
            if(empty($company_model->getVatNo()) || empty($company_model->getTinNo())){
                $customer_edit_url = Mage::getUrl('customer/account/edit');
                $customer_edit_url_html = "<a href=$customer_edit_url>here</a>";
                /*Mage::getSingleton('core/session')->addNotice('Please add Vat and Tin Number to get the Cform advantages click '.$customer_edit_url_html.' to update');*/
                $message = Mage::getModel('core/message_notice','Please add VAT and TIN Number to get the Cform advantage. Click '.$customer_edit_url_html.' to add.');
                Mage::getSingleton('core/session')->addUniqueMessages($message);
            }
        }
    }

    public function sales_view_order($observer){
        $order_id = $observer->getEvent()->getControllerAction()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($order->getQuoteId()); //$order->getQuoteId()
        // echo "<pre>"; print_r($quote->getData()); exit;
        if($quote->getId()){
            $quote->setIsActive(1)
                //->setReservedOrderId(null)
                  ->save();
            Mage::getSingleton('checkout/session')
                ->replaceQuote($quote);
            // ->unsLastRealOrderId();
        }
    }
}