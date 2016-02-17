<?php
/*------------------------------------------------------------------------
 # SM Search Box Pro - Version 1.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Searchboxpro_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function __construct(){
		$this->defaults = array(
			/* General setting */
			'isenabled'		=> '1',
			'show_popular'		=> '1',
			'limit_popular'		=> '5',
			'show_more' 		=> '1',
			'more_text' 		=> 'More++',
			'show_advanced'		=> '1',			
			'pretext' 		=> '',
			'posttext' 		=> ''
		);
	}

	function get($attributes=array())
	{
		$data = $this->defaults;
		$general 					= Mage::getStoreConfig("searchboxpro/general");
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		if (is_array($general))					$data = array_merge($data, $general);
		return array_merge($data, $attributes);;
	}

    public function getCategoryParamName() {
        return Mage::getModel('catalog/layer_filter_category')->getRequestVar();
    }

	public function getSuggestUrl()
	{
		return $this->_getUrl('searchboxpro/suggest/result', array(
			'_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
		));
	}

}
