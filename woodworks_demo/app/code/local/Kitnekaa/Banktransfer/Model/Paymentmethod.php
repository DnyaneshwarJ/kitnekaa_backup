<?php
// app/code/local/Envato/Custompaymentmethod/Model/Paymentmethod.php
class Kitnekaa_Banktransfer_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
  protected $_code  = 'banktransfer';
  protected $_formBlockType = 'banktransfer/form_banktransfer';
  protected $_infoBlockType = 'banktransfer/info_banktransfer';

  public function assignData($data)
  {
   
      $info = $this->getInfoInstance();
     

    if ($data->getCustUtrNumber())
    {
      $info->setCustUtrNumber($data->getCustUtrNumber());
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
     
    if (!$info->getCustUtrNumber())
    {
      $errorCode = 'invalid_data';
      $errorMsg = $this->_getHelper()->__("UTR No is a required field.\n");
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