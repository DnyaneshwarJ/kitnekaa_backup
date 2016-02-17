<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
class Sm_Deal_Helper_Data extends Mage_Core_Helper_Abstract{

	public function getDealsUrl(){
		return Mage::getUrl('deal/deal/index');
	}
}