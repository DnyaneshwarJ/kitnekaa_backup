<?php
/*------------------------------------------------------------------------
 # SM Tab Listing - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Tablisting_Model_System_Config_Source_OrderBy
{
	public function toOptionArray()
	{
		return array(
// 			array('value' => 'random', 		'label' => Mage::helper('tablisting')->__('Random')),
			array('value' => 'position',	'label' => Mage::helper('tablisting')->__('Position')),
			array('value' => 'created_at', 	'label' => Mage::helper('tablisting')->__('New Arrivals')),
			array('value' => 'name', 		'label' => Mage::helper('tablisting')->__('Name')),
			array('value' => 'price', 		'label' => Mage::helper('tablisting')->__('Price')),
			array('value' => 'top_rating', 	'label' => Mage::helper('tablisting')->__('Top Rating')),			
			array('value' => 'most_reviewed',	'label' => Mage::helper('tablisting')->__('Most Reviews')),
			array('value' => 'most_viewed',	'label' => Mage::helper('tablisting')->__('Most Visited')),
			array('value' => 'best_sales',	'label' => Mage::helper('tablisting')->__('Most Selling')),			
		);
	}
}
