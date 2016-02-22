<?php
class Kitnekaa_Credittransfer_PaymentController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }

    public function redirectAction(){

    	echo "You are in Final checkout process";
        echo "<pre>"; print_r((Mage::getSingleton('checkout/session')->getData()));
        $orderid  = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order =  Mage::getModel('sales/order')->load($orderid);
        
        $payment = Mage::getModel('sales/order_payment')->load($order->getId());

        echo "order id is - >".$orderid;
        echo "payment done ->".$payment->getCustBankName();
        
    }
    public function saveAction(){

    	echo "you are in save";
    	//var_dump($_POST);
    	$data = $this->getRequest()->getPost();
    	var_dump($data);
        $mymodel = Mage::getModel('test/test');
        $mymodel->setData($data)->save();
        $response = Mage::app()->getFrontController()->getResponse();
        $response->setRedirect($url);
        $response->sendResponse();
    }
} 