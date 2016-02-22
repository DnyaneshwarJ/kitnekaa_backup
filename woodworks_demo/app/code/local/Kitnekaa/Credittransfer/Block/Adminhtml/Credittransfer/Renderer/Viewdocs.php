<?php
class Kitnekaa_Credittransfer_Block_Adminhtml_Credittransfer_Renderer_Viewdocs extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
public function render(Varien_Object $row)
{   $comp = Mage::helper('credittransfer')->findcompany();
    $a = $row->getVerifyingCompanyId();
    $b = $row->getCompanyId();
    $c = $row->getVerifyingCompanyName();

	$financer = isset($a) ? $a : 0;
	$company =  $comp;
    $companyname = isset($c) ? $c : 0;

	$char = $financer.','.$company.',';
	$char.="'".$companyname."'";

$a = '<button type="button" onclick="mypage('.$char.');return false;">Get All</button>';  


return $a;

}

}