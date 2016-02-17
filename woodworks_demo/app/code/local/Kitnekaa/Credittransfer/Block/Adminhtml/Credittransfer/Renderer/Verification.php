<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Verification extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{
$value =  $row->getData($this->getColumn()->getIndex());


    if($value > 0)
    {
   $a = "<b style='color:red;'>Required Verification</b>";
  }else{

  	$a = "No Verification";
  }


return $a;

}

}