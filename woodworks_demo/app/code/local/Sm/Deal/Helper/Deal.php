<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/
 
class Sm_Deal_Helper_Deal extends Mage_Core_Helper_Abstract{

	public function isRssEnabled(){
		return  Mage::getStoreConfigFlag('rss/config/active') && Mage::getStoreConfigFlag('deal/deal/rss');
	}

	public function getRssUrl(){
		return Mage::getUrl('deal/deal/rss');
	}

	public function getUseBreadcrumbs(){
		return Mage::getStoreConfigFlag('deal/deal/breadcrumbs');
	}
}