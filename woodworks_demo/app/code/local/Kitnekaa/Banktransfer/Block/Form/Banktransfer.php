<?php
// app/code/local/Envato/Custompaymentmethod/Block/Form/Custompaymentmethod.php
class Kitnekaa_Banktransfer_Block_Form_Banktransfer extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('banktransfer/form/banktransfer.phtml');
  }
}