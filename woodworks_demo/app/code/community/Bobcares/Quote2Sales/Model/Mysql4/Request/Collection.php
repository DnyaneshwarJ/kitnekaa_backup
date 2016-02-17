<?php
class Bobcares_Quote2Sales_Model_Mysql4_Request_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('quote2sales/request');
	}
}