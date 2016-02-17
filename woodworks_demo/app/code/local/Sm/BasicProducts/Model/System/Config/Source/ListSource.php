<?php
/*------------------------------------------------------------------------
 # SM Basic Products - Version 1.0.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_BasicProducts_Model_System_Config_Source_ListSource
{
	public function toOptionArray()
	{
		return array(
			array('value' => 'catalog',     'label' => Mage::helper('basicproducts')->__('Catalog')),
			array('value' => 'media',       'label' => Mage::helper('basicproducts')->__('Media')),
			array('value' => 'ids',         'label' => Mage::helper('basicproducts')->__('Product IDs to Exclude'))
		);
	}
}
