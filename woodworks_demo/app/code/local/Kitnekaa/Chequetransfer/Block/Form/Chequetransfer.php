<?php

class Kitnekaa_Chequetransfer_Block_Form_Chequetransfer extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('chequetransfer/form/chequetransfer.phtml');
  }
}