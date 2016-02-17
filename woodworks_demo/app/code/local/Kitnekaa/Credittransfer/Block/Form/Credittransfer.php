<?php

class Kitnekaa_Credittransfer_Block_Form_Credittransfer extends Mage_Payment_Block_Form
{	    
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('credittransfer/form/credittransfer.phtml');
  }
}