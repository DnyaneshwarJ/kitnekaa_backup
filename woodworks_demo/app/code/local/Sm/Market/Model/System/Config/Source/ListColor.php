<?php
/*------------------------------------------------------------------------
 # SM Market - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Market_Model_System_Config_Source_ListColor
{
	public function toOptionArray()
	{	
		return array(
		array('value'=>'yellow', 'label'=>Mage::helper('market')->__('Yellow')),
		array('value'=>'blue', 'label'=>Mage::helper('market')->__('Blue')),
		array('value'=>'tangerine', 'label'=>Mage::helper('market')->__('Tangerine')),
		array('value'=>'emerald', 'label'=>Mage::helper('market')->__('Emerald')),
		array('value'=>'green', 'label'=>Mage::helper('market')->__('Green'))
		);
	}
}
