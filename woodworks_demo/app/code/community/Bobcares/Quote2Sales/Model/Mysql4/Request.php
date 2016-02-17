<?php
class Bobcares_Quote2Sales_Model_Mysql4_Request extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('quote2sales/request', 'request_id'); // id refers to the primary key of the request table
	}
}