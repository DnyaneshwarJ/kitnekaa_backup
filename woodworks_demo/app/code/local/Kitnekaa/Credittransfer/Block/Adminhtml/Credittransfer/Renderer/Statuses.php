<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Statuses extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{
    $verified = $row->getVerified();
   $img = "<img src='".Mage::getBaseUrl('skin')."adminhtml/default/default/images/checkmark.ico' width='20' height='20' align='middle'/><span style='align:middle;'>Verified</span>";
if($verified == 1){
	$a = $img;
	
}else{
 $a ="<img src='".Mage::getBaseUrl('skin')."adminhtml/default/default/images/cross.ico' width='20' height='20'/>Not Verified";
	
}


return $a;



}
}