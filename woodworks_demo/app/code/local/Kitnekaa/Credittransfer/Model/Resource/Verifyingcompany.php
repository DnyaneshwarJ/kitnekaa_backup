<?php

class Kitnekaa_Credittransfer_Model_Resource_Verifyingcompany extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('credittransfer/verifyingcompany', 'verifying_company_id');
	}

}