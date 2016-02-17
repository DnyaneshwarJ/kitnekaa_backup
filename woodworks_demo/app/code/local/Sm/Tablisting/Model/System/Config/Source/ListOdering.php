<?php
/*------------------------------------------------------------------------
 # SM Tab Listing - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Tablisting_Model_System_Config_Source_ListOdering
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'id', 'label'=>Mage::helper('tablisting')->__('ID')),
		array('value'=>'title', 'label'=>Mage::helper('tablisting')->__('Title')),
		array('value'=>'price', 'label'=>Mage::helper('tablisting')->__('Most Views')),
		array('value'=>'created', 'label'=>Mage::helper('tablisting')->__('Recently Added')),
		array('value'=>'random', 'label'=>Mage::helper('tablisting')->__('Random')),
		);
	}
}
