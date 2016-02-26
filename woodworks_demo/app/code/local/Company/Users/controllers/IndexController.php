<?php
class Company_Users_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Add Users"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("add users", array(
                "label" => $this->__("Add Users"),
                "title" => $this->__("Add Users")
		   ));

      $this->renderLayout(); 
	  
    }

    public function newAddressAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}