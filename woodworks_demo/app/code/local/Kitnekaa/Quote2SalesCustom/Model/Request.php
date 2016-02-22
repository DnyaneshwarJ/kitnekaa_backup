<?php

class Kitnekaa_Quote2SalesCustom_Model_Request extends Bobcares_Quote2Sales_Model_Request {
    const QUOTE_PRODUCT_STATUS_ACTIVE=1;
    const QUOTE_PRODUCT_STATUS_DOES_NOT_EXIST=2;
    const XML_PATH_EMAIL_RECIPIENT = 'quotes/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'quotes/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'quotes/email/email_template';
    protected $_current_request_id;
    protected $_bcc_emails;
    public function delete()
    {
        $_request_id=$this->getRequestId();
        Mage::dispatchEvent('delete_quote_request_before',array('request_id'=>$_request_id));
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_read');

        $req_products=Mage::getModel('quote2salescustom/requestproducts')->getCollection()
            ->addFieldToFilter('request_id',$_request_id);

        $path=Mage::helper('quote2sales')->getQuoteAttachmentUploadPath();

        foreach($req_products as $products)
        {

            $attachments = Mage::getModel('quote2salescustom/requestitemfiles')
                ->getCollection()
                ->addFieldToFilter('quote_list_id',$products->getId());

            foreach($attachments as $attachment)
            {
                unlink($path . $attachment->getFileName());
            }

            $conn->delete(
                $coreResource->getTableName('quote2salescustom/requestitemfiles'),
                array('quote_list_id= ? '=>$products->getId())
            );
        }

        $conn->delete(
            $coreResource->getTableName('quote2salescustom/requestproducts'),
            array('request_id= ? '=>$this->getRequestId())
        );

        parent::delete();

        Mage::dispatchEvent('delete_quote_request_after',array('request_id'=>$_request_id));
    }

    public function getRequestProducts()
    {
        return Mage::getModel('quote2salescustom/requestproducts')->getCollection()
            ->addFieldToFilter('request_id',$this->getRequestId());
    }

    public function getRequestQuoteAttachments($quote_list_id)
    {
        return Mage::getModel('quote2salescustom/requestitemfiles')->getCollection()
            ->addFieldToFilter('quote_list_id',$quote_list_id);
    }

    public function save()
    {
        $data=parent::getData();

        if($data['request_quote'])
        {
            $request_quote_products =$data['shopp_list_items'];
            $item_option_labels=$_POST['item_option_labels'];
            $item_option_values=$_POST['item_option_values'];
            $item_options=array("labels"=>$item_option_labels,"values"=>$item_option_values);
            $upload_files = $data['upload_files'];
            $request_quote = $data['request_quote'];
            $request_type=$request_quote['request_type'];
            Mage::dispatchEvent('save_quote_request_before',array('quote_request'=>$request_quote,'quote_items'=>$request_quote_products));
            if($request_quote['delivery_location'][0])
            {
                $request_quote = array('deliverylocation'=> $request_quote['delivery_location'][0],
                    'billing_address_id'=>$request_quote['billing_address_id'][0]);
            }elseif($request_quote_products['delivery_location'][0])
            {
                $request_quote = array('deliverylocation'=> $request_quote_products['delivery_location'][0],
                    'billing_address_id'=>$request_quote_products['billing_address_id'][0]);
            }
            $parent_customer = Mage::getSingleton('customer/session')->getCustomer();
            $customer = Mage::helper('users')->getCurrentCompanyUser();
            $request_quote['customer_id']=$parent_customer->getId();
            $request_quote['status']="Waiting";
            $request_quote['name']=Mage::helper('users')->getUserFullName($customer);
            $request_quote['email']=$customer->getEmail();
            $request_quote['phone']=Mage::helper('shoppinglist')->getAddressContactNo($request_quote['deliverylocation']);
            $request_quote['company_id']=$parent_customer->getCompanyId();
            $request_quote['request_type']=$request_type;

            parent::setData($request_quote);
            $request_id = parent::save()->getId();
            $this->_current_request_id=$request_id;
            $this->setQuoteRequestProducts($request_quote_products,$item_options,$request_id,$upload_files);
            Mage::dispatchEvent('save_quote_request_after',array('request_id'=>$request_id,'quote_request'=>$request_quote,'quote_items'=>$request_quote_products));
        }
        else
        {
            parent::save();
        }
    }


    public function setQuoteRequestProducts($request_quote_products,$item_options,$request_id,$upload_files=FALSE)
    {
        $shopp_list_attachments=array();
        foreach($request_quote_products['product_id'] as $k=>$qproduct_id)
        {

            $data_req_products=array(
                "request_id"=>$request_id,
                "item_name"=>$request_quote_products["item_name"][$k],
                "product_id"       => $qproduct_id,
                "target_price"     => $request_quote_products["target_price"][$k],
                "when_need"        => $request_quote_products["when_need"][$k],
                "sku"              => $request_quote_products["sku"][$k],
                "qty"              => $request_quote_products["qty"][$k],
                "frequency"        => $request_quote_products["frequency"][$k],
                "comment"          => $request_quote_products["comment"][$k]
            );

            if($qproduct_id)
            {
                $data_req_products['status']=self::QUOTE_PRODUCT_STATUS_ACTIVE;
            }
            else
            {
                $data_req_products['status']=self::QUOTE_PRODUCT_STATUS_DOES_NOT_EXIST;
            }

            if(!is_null($item_options['labels']) && !empty($item_options['labels']) && count($item_options['labels'])>0)
            {
                $json_item_options=json_encode(array('labels'=>$item_options['labels'][$qproduct_id],'values'=>$item_options['values'][$qproduct_id]));
                $data_req_products['item_options']=$json_item_options;
            }


            $quote_product_list_id=Mage::getModel('quote2salescustom/requestproducts')->setData($data_req_products)->save()->getId();

            if($upload_files){
                $path = $this->getQuoteAttachmentUploadPath();
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $files = $_FILES['quote_files'];

                try{
                    foreach ($files['name'] as $k => $file_name) {
                        $uploader = new Varien_File_Uploader(array('name'     => $file_name,
                            'type'     => $files['type'][$k],
                            'tmp_name' => $files['tmp_name'][$k],
                            'error'    => $files['error'][$k],
                            'size'     => $files['size'][$k])); //load class
                        $uploader->setAllowedExtensions(array('pdf', 'jpg', 'jpeg', 'png')); //Allowed extension for file
                        $uploader->setAllowCreateFolders(TRUE); //for creating the directory if not exists
                        $uploader->setAllowRenameFiles(TRUE); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
                        $uploader->setFilesDispersion(FALSE);
                        $uploader->save($path, $file_name);
                        $quote_list_files = Mage::getModel('quote2salescustom/requestitemfiles');
                        $quote_list_files->setQuoteListId($quote_product_list_id);
                        $quote_list_files->setFileName($uploader->getUploadedFileName());
                        $quote_list_files->save();

                    }
                }catch (Exception $e)
                {
                    Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__($e->getMessage()));
                }

            }else{
                $shopp_list_attachments[$quote_product_list_id] = Mage::getModel('shoppinglist/shoppinglistfiles')
                    ->getCollection()
                    ->addFieldToFilter('list_item_id', $request_quote_products["id"][$k]);
            }

        }
        if($upload_files){}
        else
        {
            $this->mapShoppingFilesWithQuoteFiles($shopp_list_attachments);
        }
    }

    public function mapShoppingFilesWithQuoteFiles($shopp_list_attachments)
    {
        $path=$this->getQuoteAttachmentUploadPath();
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if(count($shopp_list_attachments)>0)
        {
            foreach($shopp_list_attachments as $quote_list_id=>$shoppinglistfiles)
            {
                if(count($shoppinglistfiles->getData())>0)
                {
                    foreach($shoppinglistfiles as $file)
                    {
                        Mage::getModel('quote2salescustom/requestitemfiles')->setFileName($file->getFileName())
                            ->setQuoteListId($quote_list_id)->save();
                        $source = Mage::helper('shoppinglist')->getAttachmentUploadPath().$file->getFileName();
                        $destination=$path.$file->getFileName();
                        copy($source,$destination);
                    }
                }
            }
        }
    }
    public  function getQuoteAttachmentUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'quotefiles/';
    }

    public function getQuoteAttachmentUploadPath()
    {
        return  Mage::getBaseDir() . DS . 'media' . DS . 'quotefiles' . DS;
    }

    public function sendEmail()
    {

        Mage::dispatchEvent('send_quote_request_email_before',array('quote_request'=>$this));

        $customer = Mage::helper('users')->getCurrentCompanyUser();
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        /* Changed the mail template for fixing the request not submitted issue */
        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE);
        $sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
        $res = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);
        $mailTemplate = Mage::getModel('core/email_template');
        //$mailTemplate->setReplyTo($sender);

        if(count($this->getBccEmails())>0)
        {
            $mailTemplate->addBcc($this->getBccEmails());
        }

        $company=Mage::getModel('users/company')->load($customer->getCompanyId());
        $quote_request_products=$this->getRequestProducts();
        //var_dump()
        $params=array('company'=>$company,
            'customerName' =>Mage::helper('users')->getUserFullName($customer),
            'customer' => $customer,
            'request_id'=>$this->_current_request_id,
            'quote_request_products'=>$quote_request_products);

        $mailTemplate->sendTransactional($templateId, $sender,$res,null,$params,Mage::app()->getStore()->getId());
        /* If mail not sent */
        if (!$mailTemplate->getSentSuccess()) {
            throw new Exception();
        }
        $translate->setTranslateInline(true);
    }

    public function setBccEmails($bcc_emails)
    {
        $this->_bcc_emails=$bcc_emails;
    }

    public function getBccEmails()
    {
        return $this->_bcc_emails;
    }
}
