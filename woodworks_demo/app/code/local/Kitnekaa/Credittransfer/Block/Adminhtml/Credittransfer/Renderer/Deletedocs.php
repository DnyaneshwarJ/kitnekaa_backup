<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Deletedocs extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{
    $id = $row->getId();
  
   $customer_id = $row->getCustomerId();
   $verifying_company_id = $row->getVerifyingCompanyId();
 $financer = Mage::getModel('credittransfer/verifyingcompany')->load($verifying_company_id);
 $financer = "'".$financer->getVerifyingCompanyName()."'";
$a = '<button type="button" onclick="docdelete('.$id.','.$financer.','.$customer_id.','.$verifying_company_id.');return false;">Delete</button>';  


return $a;

}

}