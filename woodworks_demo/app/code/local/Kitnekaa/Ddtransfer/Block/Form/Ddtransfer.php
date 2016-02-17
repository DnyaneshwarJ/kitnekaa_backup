<?php

class Kitnekaa_Ddtransfer_Block_Form_Ddtransfer extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('ddtransfer/form/ddtransfer.phtml');
  }
}