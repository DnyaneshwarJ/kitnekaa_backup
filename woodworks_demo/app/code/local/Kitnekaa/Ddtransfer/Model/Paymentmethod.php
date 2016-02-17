<?php

class Kitnekaa_Ddtransfer_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'ddtransfer';
  protected $_formBlockType = 'ddtransfer/form_ddtransfer';
  protected $_infoBlockType = 'ddtransfer/info_ddtransfer';


 
  public function assignData($data)
  {
   
      $info = $this->getInfoInstance();
     

    if ($data->getCustDdNumber())
    {
      $info->setCustDdNumber($data->getCustDdNumber());
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
     
    if (!$info->getCustDdNumber())
    {
      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("DD Number is a required field.\n");
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