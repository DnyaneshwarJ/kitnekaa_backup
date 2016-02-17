<?php
/*------------------------------------------------------------------------
 # SM Market - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Market_Model_System_Config_Source_ListHeader
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'header1', 'label'=>Mage::helper('market')->__('Header default')),
		array('value'=>'header2', 'label'=>Mage::helper('market')->__('Header 2')),
		array('value'=>'header3', 'label'=>Mage::helper('market')->__('Header 3')),
		array('value'=>'header4', 'label'=>Mage::helper('market')->__('Header 4'))
		);
	}
}
