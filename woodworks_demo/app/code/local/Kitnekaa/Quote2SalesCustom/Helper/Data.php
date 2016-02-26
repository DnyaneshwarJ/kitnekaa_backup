<?php
class Kitnekaa_Quote2SalesCustom_Helper_Data extends Bobcares_Quote2Sales_Helper_Data{

	const QUOTE_PRODUCT_STATUS_ACTIVE=1;
	const QUOTE_PRODUCT_STATUS_DOES_NOT_EXIST=2;
	public function frequencyOptions()
	{
		return array(
			array('value' => 'One Time', 'label' => 'One Time'),
			array('value' => 'Daily', 'label' => 'Daily'),
			array('value' => 'Weekly', 'label' => 'Weekly'),
			array('value' => 'Fortnightly', 'label' => 'Fortnightly'),
			array('value' => 'Monthly', 'label' => 'Monthly'),
			array('value' => 'Quarterly', 'label' => 'Quarterly'),
			array('value' => 'Yearly', 'label' => 'Yearly')
		);
	}

	public function whenNeedOptions()
	{
		return array(
			array('value' => 'Now', 'label' => 'Now'),
			array('value' => '< 7 days', 'label' => '< 7 days'),
			array('value' => '< 15 days', 'label' => '< 15 days'),
			array('value' => '< 30 days', 'label' => '< 30 days'),
			array('value' => '> 30 days', 'label' => '> 30 days')
		);
	}

	public function  getFrequencyHtmlSelect($name,$select_msg=FALSE,$value=NULL)
	{
		$options = $this->frequencyOptions();
		if($select_msg){
			array_unshift($options,array('value' => '', 'label' => 'Select Purchase Frequency'));
		}
		$select = Mage::app()->getLayout()->createBlock('core/html_select');
		$select->setName($name);
		if(!is_null($value)){$select->setValue($value);}
		$select->setOptions($options);

		return $select->getHtml();
	}

	public function  getWhenNeedHtmlSelect($name,$select_msg=FALSE,$value=NULL)
	{
		$options = $this->whenNeedOptions();
		if($select_msg){
			array_unshift($options,array('value' => '', 'label' => 'Select When Needed'));
		}
		$select = Mage::app()->getLayout()->createBlock('core/html_select');
		$select->setName($name);
		if(!is_null($value)){$select->setValue($value);}
		$select->setOptions($options);

		return $select->getHtml();
	}

	public function getCustomerAddress($_address_id)
	{
		$address=Mage::getModel('customer/address')->load($_address_id);
		return "<option value='".$address->getId()."'>".$address->format('oneline')."</option>";
	}
	public function getAddressesHtmlSelect($name,$value=NULL,$label=NULL,$class=NULL)
	{
		$session=Mage::getSingleton('customer/session');
		$customer=$session->getCustomer();
		if ($session->isLoggedIn()) {
			$options = array();
			if(is_null($label))
			{
				$options[]=array(  'value' => '',
					'label' => Mage::helper('checkout')->__('Select Address'));
			}
			else
			{
				$options[]=array(  'value' => '',
					'label' => Mage::helper('checkout')->__($label));
			}

			foreach ($customer->getAddresses() as $address) {
				$options[] = array(
					'value' => $address->getId(),
					'label' => $address->format('oneline')
				);
			}

			$select = Mage::app()->getLayout()->createBlock('core/html_select');
			$select->setName($name);
			if(!is_null($class))
			{
				$select->setClass($class);
			}
			if(!is_null($value)){$select->setValue($value);}
			$select->setOptions($options);

			return $select->getHtml();
		}

		return '';
	}

	public function getCompanyById($company_id)
	{
		$company=Mage::getModel('users/company')->load($company_id);
		return  $company;
	}

	public function getOnelineAddressById($id)
	{
		$address=Mage::getModel('customer/address')->load($id);
		return $address->format('oneline');
	}

	public function setQuoteRequest($request_quote)
	{
		$parent_customer = $this->_getSession()->getCustomer();
		$customer = Mage::helper('users')->getCurrentCompanyUser();

		$request_quote['customer_id']=$parent_customer->getId();
		$request_quote['status']="Waiting";
		$request_quote['name']=Mage::helper('users')->getUserFullName($customer);
		$request_quote['email']=$customer->getEmail();
		$request_quote['phone']=Mage::helper('shoppinglist')->getAddressContactNo($request_quote['deliverylocation']);
		$request_quote['company_id']=$parent_customer->getCompanyId();
		$request_model = Mage::getModel('quote2sales/request')->setData($request_quote);
		return $id = $request_model->save()->getId();
	}

	public function setQuoteRequestProducts($request_quote_products,$request_id,$upload_files=FALSE)
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

		Mage::dispatchEvent('save_quote_request_after',array('request_id'=>$request_id));
	}

	public function getQuoteProductStatus($code)
	{
		$q_status=array(self::QUOTE_PRODUCT_STATUS_ACTIVE=>'Active',
			self::QUOTE_PRODUCT_STATUS_DOES_NOT_EXIST=>'Does Not Exist');

		return $q_status[$code];
	}

	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}

	public function getClassObject()
	{
		return new Kitnekaa_Quote2SalesCustom_Helper_Data();
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
		$parent_customer = $this->_getSession()->getCustomer();
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		/* Changed the mail template for fixing the request not submitted issue */
		$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE);
		$sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER);
		$res = Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT);

		$mailTemplate = Mage::getModel('core/email_template');
		$mailTemplate->setDesignConfig(array('area' => 'frontend'));
		$mailTemplate->setReplyTo($post['email']);

		$mailTemplate->sendTransactional(
			$templateId, $sender, $res, null, array('customerName' => $name, 'customerEmail' => $email, 'comment' => $comment, 'requestid' => $savedRequestId)
		);

		/* If mail not sent */
		if (!$mailTemplate->getSentSuccess()) {

			throw new Exception();
		}

		$translate->setTranslateInline(true);
	}
}