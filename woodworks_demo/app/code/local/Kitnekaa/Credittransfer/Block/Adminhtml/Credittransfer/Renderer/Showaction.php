<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Showaction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{
    $id = $row->getId();
     $customer_id = $row->getCustomerId();
     $doc_path = $row->getDocPath();
              $financer_name = Mage::helper('credittransfer')->financername($row->getVerifyingCompanyId());    
           $financer_name = $financer_name[0];

     $slash = '/';      
     $showurl =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .$slash.'company'.$slash.'documents'.$slash.$customer_id.$slash.$financer_name['verifying_company_name'].$slash.$doc_path;
      
//$a ="\\";
//$fin_link = str_replace($a,"/",$showurl);
       $link = "<a href='".$showurl."' target='_blank'>View</a>";
   

return $link;

}

}