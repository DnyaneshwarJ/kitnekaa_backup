<?php
class Company_Users_Block_Adminhtml_Customer_Edit_Tab_Action 
extends Mage_Adminhtml_Block_Template 
implements Mage_Adminhtml_Block_Widget_Tab_Interface{
	public function __construct()
	{
		$this->setTemplate('company/userscompanytab.phtml');
	}

	public function getCustomtabInfo()
	{
		$customer = Mage::registry('current_customer');
		$customtab = 'My Content Goes Here';
		return $customtab;
	}

	public function getTabLabel()
	{
		return $this->__('Company Information');
	}

	public function getTabTitle()
	{
		return $this->__('Company Information');
	}

	public function canShowTab()
	{
		//$customer = Mage::registry('current_customer');
		return true;
	}

	public function isHidden()
	{
		return false;
	}

	public function getAfter()
	{
		return 'account';
	}
}