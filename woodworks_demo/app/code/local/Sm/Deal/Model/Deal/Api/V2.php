<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Model_Deal_Api_V2 extends Sm_Deal_Model_Deal_Api{

	public function info($dealId){
		$result = parent::info($dealId);
		$result = Mage::helper('api')->wsiArrayPacker($result);
		foreach ($result->products as $key => $value) {
			$result->products[$key] = array('key' => $key, 'value' => $value);
		}
		return $result;
	}
}
