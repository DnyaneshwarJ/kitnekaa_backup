<?php

require 'Bobcares/Quote2Sales/Helper/Request.php';

class Bobcares_Quote2Sales_RequestController extends Mage_Core_Controller_Front_Action {

    const XML_PATH_EMAIL_RECIPIENT = 'quotes/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'quotes/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'quotes/email/email_template';

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    public function preDispatch() {
        parent::preDispatch();

        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction() {
        $requestModel = Mage::getModel('quote2sales/request');
        $customerId = $this->_getSession()->getCustomerId();

        $requests = $requestModel->getAllRequests($customerId);
        Mage::register('requests_all', $requests, true);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function saveRequestAction() {
        $comment = $this->getRequest()->getParam("comment");
        $name = $this->getRequest()->getParam("name");
        $email = $this->getRequest()->getParam("email");
        $phone = $this->getRequest()->getParam("telephone");
        $product_id = $this->getRequest()->getParam("product_id");
        $deliverylocation = $this->getRequest()->getParam("deliverylocation");
        $demo = $this->getRequest()->getParam("demo");
        $paymentterms = $this->getRequest()->getParam("paymentterms");
        $shippingmethod = $this->getRequest()->getParam("shippingmethod");
        $deliverydate = $this->getRequest()->getParam("deliverydate");
        $shipping = $this->getRequest()->getParam("shipping");
        $ver = $this->getRequest()->getParam("ver");
        $paymentroad = $this->getRequest()->getParam("paymentroad");
        //Returns a string with backslashes before characters that need to be escaped.
        $comment = addcslashes($comment, '"');
        $comment = addcslashes($comment, "'");

        if ($comment) {

            try {
                //$request = new Request();
                /* $customerId = $this->_getSession()->getCustomerId();
                 $request->customer_id = $customerId;
                 $request->name = $name;
                 $request->comment = $comment;
                 $request->email = $email;
                 $request->phone = $phone;
                 $request->deliverylocation = $deliverylocation;
                 $request->product_id = $product_id;*/
                //$savedRequestId = $request->create(false);
                $customerId = $this->_getSession()->getCustomerId();
                $data = array(
                    "customer_id" => $customerId,
                    "status" => "Waiting",
                    "name" => $name,
                    "email" => $email,
                    "phone" => $phone,
                    "comment" => $comment,
                    "product_id" => $product_id,
                    "target_price" => $demo,
                    "ver" => $ver,
                    "deliverylocation" => $deliverylocation,
                    "paymentterms" => $paymentterms,
                    "shippingmethod" => $shippingmethod,
                    "deliverydate" => $deliverydate,
                    "paymentroad" => $paymentroad,

                );




                $model = Mage::getModel('quote2sales/request')->setData($data);
                try{
                    $id = $model->save()->getId();
                    //echo "Data inserted successfully";
                }catch(Exception $e){
                    echo $e->getMessage();
                }


                /* If data not saved correctly */

                $viewRequestsURL = Mage::getUrl('*/*/');
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('quote2sales')->__('Your request was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_getSession()->addSuccess($this->__('Request for Quote was successfully saved. <a href="' . $viewRequestsURL . '">View all saved requests</a>'));

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
                    $templateId, $sender, $res, null, array('customerName' => $name, 'customerEmail' => $email,'demo' => $demo, 'telephone' => $phone, 'comment' => $comment, 'requestid' => $savedRequestId)
                );

                /* If mail not sent */
                if (!$mailTemplate->getSentSuccess()) {

                    throw new Exception();
                }

                $translate->setTranslateInline(true);

            } catch (Mage_Core_Exception $e) {
                print_r($e);
                echo "fsfgsdfg";
                exit;
                Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__('Unable to submit your request. Please, try again later'));
            } catch (Exception $e) {
                print_r($e);
                echo "exc22";
                exit;
                Mage::getSingleton('customer/session')->addError(Mage::helper('quote2sales')->__('Unable to submit your request. Please, try again later'));
            }
        }
        $referer = $this->_getRefererUrl();
        $this->_redirectUrl($referer);
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl() {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }


    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('adminhtml/quote2sales/request');
    }

}
