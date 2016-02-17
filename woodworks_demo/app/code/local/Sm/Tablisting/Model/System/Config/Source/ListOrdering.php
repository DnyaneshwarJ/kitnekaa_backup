<?php
/*------------------------------------------------------------------------
 # SM Tab Listing - Version 1.1
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Tablisting_Model_System_Config_Source_ListOrdering
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'category', 'label'=>Mage::helper('tablisting')->__('Category Tab')),
		array('value'=>'order', 'label'=>Mage::helper('tablisting')->__('Product Order Tab')),
		);
	}
}
