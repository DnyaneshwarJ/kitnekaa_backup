<?php

/**
 * Adminhtml customer action tab
 *
 */
class Kitnekaa_Customertab_Block_Adminhtml_Customer_Edit_Tab_Action extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
	public function __construct() {
		$this->getCompanyNames();
		$customer = Mage::registry ( 'current_customer' );
		$this->setTemplate ( 'customertab/action.phtml' );
	}
	public function getCustomtabInfo() {
		
		$customer = Mage::registry ( 'current_customer' );
		
		$customtab = 'My Custom tab Action Contents Here';
		return $customtab;
	
	}
	
	/**
	 * Return Tab label
	 *
	 * @return string
	 */
	public function getTabLabel() {
		return $this->__ ( 'Authorize and Assign Company' );
	}
	
	/**
	 * Return Tab title
	 *
	 * @return string
	 */
	public function getTabTitle() {
		return $this->__ ( 'Authorize and Assign Company' );
	}
	
	/**
	 * Can show tab in tabs
	 *
	 * @return boolean
	 */
	public function canShowTab() {
		$customer = Mage::registry ( 'current_customer' );
		return ( bool ) $customer->getId ();
	}
	
	/**
	 * Tab is hidden
	 *
	 * @return boolean
	 */
	public function isHidden() {
		return false;
	}
	
	/**
	 * Defines after which tab, this tab should be rendered
	 *
	 * @return string
	 */
	public function getAfter() {
		return 'tags';
	}
	
	public function getCompanyNames()
	{
		$customer = Mage::registry ( 'current_customer' );
		$companyRm = Mage::getResourceModel(
    			'kitnekaa_participant/company'
    	);
		$companies= $companyRm->getCompanies();
    	$html = "<option value='0'>Selet Participant</option>";
    	foreach($companies as $company)
    	{
    		if($customer->getCompanyId()==$company['id'])
    		{
    			$html.="<option value='".$company['id']."' selected>".$company['company_name']."</option>";
    		}else{
    			$html.="<option value='".$company['id']."'>".$company['company_name']."</option>";
    		}
    	}
    	return $html;
	}
	public function getActiveStatus()
	{
		$customer = Mage::registry ( 'current_customer' );
		return ($customer->getIsActive())?'checked':'';
	}
	
	public function getUserCompanyName(){
		$customer = Mage::registry ( 'current_customer' );
		return "Kitnekaa.com";
	}
}
?>