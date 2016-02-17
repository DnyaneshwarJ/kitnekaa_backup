<?php

class Kitnekaa_Credittransfer_Block_Info_Credittransfer extends Mage_Payment_Block_Info
{
  protected function _prepareSpecificInformation($transport = null)
  {
    if (null !== $this->_paymentSpecificInformation) 
    {
      return $this->_paymentSpecificInformation;
    }
     
    $data = array();
    
    $Financer = $this->getInfo()->getadditionalInformation();

   $financer_name = Mage::helper('credittransfer')->financername($Financer['financer']);
  $financer_name = $financer_name[0];
  $companyName = str_replace("_", " ", $financer_name['verifying_company_name']);
    
    if ($this->getInfo()->getVatNo()) 
    {
      $data[Mage::helper('payment')->__('VAT Number')] = $this->getInfo()->getVatNo();
    }
     
    if ($this->getInfo()->getCustTinNo()) 
    {
      $data[Mage::helper('payment')->__('TIN Number')] = $this->getInfo()->getTinNo();
    }
 
      $data[Mage::helper('payment')->__('Verification Status')] = "Under Verification";
      $data[Mage::helper('payment')->__('Financer')] = $companyName;
      $transport = parent::_prepareSpecificInformation($transport);
     
    return $transport->setData(array_merge($data, $transport->getData()));
  }
}