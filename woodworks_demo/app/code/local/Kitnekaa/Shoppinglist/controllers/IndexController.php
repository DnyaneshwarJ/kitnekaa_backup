<?php

//require_once "Mage/Wishlist/controllers/IndexController.php";
class Kitnekaa_Shoppinglist_IndexController extends Mage_Core_Controller_Front_Action
{

    /**
     * Checking if user is logged in or not
     * If not logged in then redirect to customer login
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $is_ajax = Mage::app()->getRequest()->getPost('is_ajax');


        if (!Mage::getSingleton('customer/session')->authenticate($this)) {

            if ($is_ajax)
            {
                echo json_encode(array('success'=>FALSE,'msg'=>'You are not logged in!'));
                exit;
            }
            else
            {
                $this->setFlag('', 'no-dispatch', TRUE);
            }

        }
    }

    public function indexAction()
    {
        $collection = Mage::getModel('shoppinglist/shoppinglist')->getCollection();
        $block = $this->getLayout()->createBlock('shoppinglist/index');
        Mage::register('current_list_id', $block->getSelectedList()->getListId());
        // echo $block;die;

        $this->loadLayout();
        $this->renderLayout();
    }

    public function createShoppingListAction()
    {
        $customer = Mage::helper('users')->getCurrentCompanyUser();

        $data = $this->getRequest()->getPost('shopplist');
        $shopp_list = Mage::getModel('shoppinglist/shoppinglist');

        $shopp_list->setListName($data['list_name']);
        $shopp_list->setCompanyId($data['company_id']);
        $shopp_list->setStatus(1);
        $shopp_list->setCreatedTime(strtotime("now"));
        $shopp_list->setCreatedBy(Mage::helper('users')->getUserFullName($customer));
        if ($shopp_list->save()) {
            $result['msg'] = "Shopping List Created Successfully!";
        } else {
            $result['msg'] = "Error occurred while creating list!";
        }

        /* $layout = $this->getLayout();
         $update = $layout->getUpdate();
         $update->load('ajax_msg_handle');
         $layout->generateXml();
         $layout->generateBlocks();
         $output = $layout->getOutput();
         $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('error' => $output)));*/

        $result['block'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/shopp_lists.phtml')->toHtml();
        $result['select'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/shopp_list_select.phtml')->toHtml();

        echo json_encode($result);
    }

    public function getShoppingListAction()
    {
        $result['block'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/shopp_lists.phtml')->toHtml();
        $result['select'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/shopp_list_select.phtml')->toHtml();
        echo json_encode($result);
    }

    public  function getShoppingListNamesAction()
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn())
        {
            echo json_encode(array('success'=>true,'html'=>$this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/min_shopping_list_inner.phtml')->toHtml()));
        }
        exit;
    }
    public function addToShoppingListAction()
    {
        $customer = Mage::helper('users')->getCurrentCompanyUser();

        $shopp_list_id = $this->getRequest()->getPost('list_id');
        $pro_ids = $this->getRequest()->getPost('pro_ids');
        //print_r($pro_ids);die;
        //$productIds = array(59,80);
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => $pro_ids))
            ->addAttributeToSelect($attributes);

        foreach ($collection as $data) {
            $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems');
            $shopp_list_item->setListId($shopp_list_id);
            $shopp_list_item->setSku($data->getSku());
            $shopp_list_item->setItemName($data->getName());
            $shopp_list_item->setAddedBy(Mage::helper('users')->getUserFullName($customer));
            $shopp_list_item->setPrice($data->getPrice());
            $shopp_list_item->setProductId($data->getEntityId());
            $shopp_list_item->save();
        }

        $shopp_list = Mage::getModel('shoppinglist/shoppinglist');
        $shopp_list->setUpdatedBy(Mage::helper('users')->getUserFullName($customer))
            ->setUpdateTime(strtotime("now"))
            ->setListId($shopp_list_id)->save();

        $result['msg'] = "Products Added in shopping list!";
        echo json_encode($result);

    }

    public function getShoppListItemsAction()
    {
        $shopp_list_id = $this->getRequest()->getPost('list_id');
        Mage::register('current_list_id', $shopp_list_id);
        $result['block'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/list_items_inner.phtml')->toHtml();
        echo json_encode($result);
        exit;
    }

    public function saveShoppingItemsAction()
    {
        $customer = Mage::helper('users')->getCurrentCompanyUser();
        $shopp_items = $this->getRequest()->getPost('shopp_list_items');
        $list_id = NULL;
        //var_dump($shopp_items);die;
        foreach ($shopp_items['id'] as $k => $_id) {
            $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems');
            $shopp_list_item->setId($_id);
            $shopp_list_item->setQty($shopp_items['qty'][$k]);
            $shopp_list_item->setWhenNeed(mysql_real_escape_string($shopp_items['when_need'][$k]));
            $shopp_list_item->setFrequency($shopp_items['frequency'][$k]);
            $shopp_list_item->setTargetPrice($shopp_items['target_price'][$k]);
            $shopp_list_item->setQty($shopp_items['qty'][$k]);
            $shopp_list_item->setComment($shopp_items['comment'][$k]);
            $shopp_list_item->setDeliveryLocation($shopp_items['delivery_location'][$k]);
            $shopp_list_item->setBillingAddressId($shopp_items['billing_address_id'][$k]);
            $shopp_list_item->save();
            $list_id = $shopp_items['list_id'][$k];
        }
        $shopp_list = Mage::getModel('shoppinglist/shoppinglist');
        $shopp_list->setUpdatedBy(Mage::helper('users')->getUserFullName($customer))
            ->setUpdateTime(strtotime("now"))
            ->setListId($list_id)->save();
        $result['msg'] = "Shopping List Updated Successfully!";
        echo json_encode($result);
    }

    public function  addToCartAction()
    {
        $pro_ids = $this->getRequest()->getPost('pro_ids');
        $qtys = $this->getRequest()->getPost('qtys');

        foreach ($pro_ids as $k => $prod_id) {
            $_product = Mage::getModel('catalog/product')->load($prod_id);
            $cart = Mage::getModel('checkout/cart');
            $cart->init();
            $cart->addProduct($_product, array('qty' => $qtys[$k]));
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(TRUE);
        }


        $result['msg'] = "Items added to the shopping cart!";
        echo json_encode($result);
    }

    public function setSingleQuoteAction()
    {
        $result=$this->_setMultiQuote();
        echo json_encode($result);
    }

    public function addToCartShopplistAction()
    {
        $pro_ids = $this->getRequest()->getPost('pro_ids');
        $qtys = $this->getRequest()->getPost('qtys');

        foreach ($pro_ids as $k => $prod_id) {
            $_product = Mage::getModel('catalog/product')->load($prod_id);
            $cart = Mage::getModel('checkout/cart');
            $cart->init();
            $cart->addProduct($_product, array('qty' => $qtys[$k]));
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(TRUE);
        }


        $result['msg'] = "Items added to the shopping cart successfully!";
        echo json_encode($result);
    }

    public function removeItemFromListAction()
    {
        $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems');
        $item_id = $this->getRequest()->getPost('item_id');
        $shopp_list_item->setId($item_id)->delete();
        $result['msg'] = "Item removed successfully!";
        echo json_encode($result);
    }

    public function removeSelectedItemFromListAction()
    {
        $item_ids = $this->getRequest()->getPost('item_ids');
        foreach ($item_ids as $k => $item_id) {
            $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems');
            $shopp_list_item->setId($item_id)->delete();
        }
        $result['msg'] = "Items removed successfully!";
        echo json_encode($result);
    }

    public function removeSelectedListAction()
    {
        $list_id = $this->getRequest()->getPost('list_id');

        $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems')->getCollection()
            ->addFieldToFilter('list_id', $list_id);
        foreach ($shopp_list_item as $k => $item) {
            $item->delete();
        }

        $shopp_list = Mage::getModel('shoppinglist/shoppinglist');
        $shopp_list->setListId($list_id)->delete();

        $result['msg'] = "List Deleted Successfully!";
        echo json_encode($result);
    }

    public function getShoppListSelectAction()
    {
        $result['select'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/shopp_list_select.phtml')->toHtml();
        echo json_encode($result);
    }

    public function saveNonExistItemAction()
    {
        $this->_saveNonExistItem();
        $result['msg'] = "Added Item in Shopping List!";
        echo json_encode($result);
    }

    public function setQuoteNonExistItemAction()
    {
        $this->_saveNonExistItem();
        // $this->_setSingleQuote();
        $result=$this->_setMultiQuote();
        $result['msg'] = "Your RFQ Send Successfully!";
        echo json_encode($result);
    }

    public function getFieldsForMultiQuoteAction()
    {
        $result['fields'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/multi_products_quote.phtml')->toHtml();
        echo json_encode($result);
    }
    public function _setSingleQuote()
    {
        //var_dump($this->getRequest()->getPost('shopp_list_items'));die;
        $shopp_items = $this->getRequest()->getPost('shopp_list_items');

        $parent_customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer = Mage::helper('users')->getCurrentCompanyUser();
        foreach ($shopp_items['id'] as $k => $_id) {
            $data = array(
                "customer_id"      => $parent_customer->getId(),
                "status"           => "Waiting",
                "name"             => Mage::helper('users')->getUserFullName($customer),
                "email"            => $customer->getEmail(),
                "comment"          => $shopp_items['comment'][$k],
                "product_id"       => $shopp_items['product_id'][$k],
                "target_price"     => $shopp_items['target_price'][$k],
                "deliverylocation" => $shopp_items['delivery_location'][$k],
                "when_need"        => $shopp_items['when_need'][$k],
                "sku"              => $shopp_items['sku'][$k],
                "qty"              => $shopp_items['qty'][$k],
                "frequency"        => $shopp_items['frequency'][$k],
                "phone"            => Mage::helper('shoppinglist')->getAddressContactNo($shopp_items['delivery_location'][$k]),
                "company_id"       => $parent_customer->getCompanyId()
            );

            $model = Mage::getModel('quote2sales/request')->setData($data);
            try {
                $id = $model->save()->getId();
                //echo "Data inserted successfully";
            } catch (Exception $e) {
                echo $e->getMessage();
            }

        }


    }

    public function _setMultiQuote()
    {
        $data = Mage::app()->getRequest()->getPost();
        $data['upload_files']=TRUE;


        try {
            $request_model=Mage::getModel('quote2sales/request');
            $request_model->setData($data)->save();
            $request_model->sendEmail();
            $result["msg"]=Mage::helper('quote2sales')->__('Your request was submitted and will be responded to as soon as possible. Thank you for contacting us.');
            /*$id = Mage::helper('quote2sales')->setQuoteRequest($request_quote);
            try {
                Mage::helper('quote2sales')->setQuoteRequestProducts($request_quote_products, $id);

            } catch (Exception $e) {
                echo $e->getMessage();
            }*/
        } catch (Mage_Core_Exception $e) {

            $result["msg"]=Mage::helper('quote2sales')->__('Unable to submit your request. Please, try again later');
        }

        return $result;
    }

    public function _saveNonExistItem()
    {
        $shopp_items = $this->getRequest()->getPost('shopp_list_items');

        foreach ($shopp_items['item_name'] as $k => $_id) {
            $shopp_list_item = Mage::getModel('shoppinglist/shoppinglistitems');
            $shopp_list_item->setItemName($shopp_items['item_name'][$k]);
            $shopp_list_item->setListId($shopp_items['list_id'][$k]);
            $shopp_list_item->setSku($shopp_items['sku'][$k]);
            $shopp_list_item->setProductId(0);
            $shopp_list_item->setQty($shopp_items['qty'][$k]);
            $shopp_list_item->setWhenNeed(mysql_real_escape_string($shopp_items['when_need'][$k]));
            $shopp_list_item->setFrequency($shopp_items['frequency'][$k]);
            $shopp_list_item->setTargetPrice($shopp_items['target_price'][$k]);
            $shopp_list_item->setQty($shopp_items['qty'][$k]);
            $shopp_list_item->setComment($shopp_items['comment'][$k]);
            $shopp_list_item->setDeliveryLocation($shopp_items['delivery_location'][$k]);
            $shopp_list_item->setBillingAddressId($shopp_items['billing_address_id'][$k]);
            $shopp_list_item->save();
        }

    }

    public function testAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getShopFilesAction()
    {
        $list_item_id = $this->getRequest()->getPost('list_item_id');

        $result['filesblock'] = $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/fileupload/filescontainer.phtml')->setData('list_item_id', $list_item_id)->toHtml();
        echo json_encode($result);
    }


    public function uploadShopFilesAction()
    {

        $path = Mage::helper('shoppinglist')->getAttachmentUploadPath();
        $list_item_id = $this->getRequest()->getPost('list_item_id');
        $files = $_FILES['shop_files'];

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
            $shopp_list_files = Mage::getModel('shoppinglist/shoppinglistfiles');
            $shopp_list_files->setListItemId($list_item_id);
            $shopp_list_files->setFileName($uploader->getUploadedFileName());
            $shopp_list_files->save();

            $error[] = $files['error'][$k];
        }

        echo $error;
    }

    public function removeAttachmentAction()
    {
        $path = Mage::helper('shoppinglist')->getAttachmentUploadPath();
        $file_id = $this->getRequest()->getPost('file_id');
        $attachment = Mage::getModel('shoppinglist/shoppinglistfiles')
            ->getCollection()
            ->addFieldToFilter('file_id', $file_id)
            ->getFirstItem();
        $shopp_list_attach = Mage::getModel('shoppinglist/shoppinglistfiles');
        $shopp_list_attach->setFileId($file_id)->delete();

        unlink($path . $attachment->getFileName());
    }

    public function setMultiQuoteAction()
    {
        $result=$this->_setMultiQuote();
        echo json_encode($result);
    }

    public function seller_registrationAction()
    {
        $this->loadLayout();
        //echo $this->getLayout()->createBlock('shoppinglist/index')->setTemplate('shoppinglist/seller_registration.phtml')->toHtml();
        $this->renderLayout();
    }
}
				