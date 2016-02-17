<?php

class Kitnekaa_Chequetransfer_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'chequetransfer';
  protected $_formBlockType = 'chequetransfer/form_chequetransfer';
  protected $_infoBlockType = 'chequetransfer/info_chequetransfer';


 
  public function assignData($data)
  {
   
      $info = $this->getInfoInstance();
     

    if ($data->getCustChqNumber())
    {
      $info->setCustChqNumber($data->getCustChqNumber());
    }
     
    if ($data->getCustBankName())
    {
      $info->setCustBankName($data->getCustBankName());
    }

    if ($data->getCustBranchName())
    {
      $info->setCustBranchName($data->getCustBranchName());
    }

     if ($data->getCustTransDate())
    {
      $info->setCustTransDate($data->getCustTransDate());
    }
 
    return $this;
  }
 
  public function validate()
  {
    parent::validate();
    $info = $this->getInfoInstance();
     
    if (!$info->getCustChqNumber())
    {
      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("Check Number is a required field.\n");
    }
     
    if (!$info->getCustBankName())
    {
      $errorCode = 'invalid_data';
      $errorMsg .= $this->_getHelper()->__('Bank Name is a required field.');
    }
 
    if ($errorMsg) 
    {
      Mage::throwException($errorMsg);
    }
 
    return $this;
  }

}