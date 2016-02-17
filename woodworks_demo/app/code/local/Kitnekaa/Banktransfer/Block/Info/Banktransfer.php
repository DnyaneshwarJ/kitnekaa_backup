<?php
// app/code/local/Envato/Custompaymentmethod/Block/Info/Custompaymentmethod.php
class Kitnekaa_Banktransfer_Block_Info_Banktransfer extends Mage_Payment_Block_Info
{
  protected function _prepareSpecificInformation($transport = null)
  {
    if (null !== $this->_paymentSpecificInformation) 
    {
      return $this->_paymentSpecificInformation;
    }
     
    $data = array();
    if ($this->getInfo()->getCustUtrNumber()) 
    {
      $data[Mage::helper('payment')->__('Customer Utr Number')] = $this->getInfo()->getCustUtrNumber();
    }
     
    if ($this->getInfo()->getCustBankName()) 
    {
      $data[Mage::helper('payment')->__('Customer Bank Name')] = $this->getInfo()->getCustBankName();
    }

     if ($this->getInfo()->getCustBranchName()) 
    {
      $data[Mage::helper('payment')->__('Customer Branch Name')] = $this->getInfo()->getCustBranchName();
    }

    if ($this->getInfo()->getCustTransDate()) 
    {
      $data[Mage::helper('payment')->__('Transaction Date')] = $this->getInfo()->getCustTransDate();
    }
      
    $transport = parent::_prepareSpecificInformation($transport);
     
    return $transport->setData(array_merge($data, $transport->getData()));
  }
}