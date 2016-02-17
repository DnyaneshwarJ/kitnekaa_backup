<?php
/*------------------------------------------------------------------------
 # SM Market - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Market_Model_System_Config_Source_ListBodyFont
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'', 'label'=>Mage::helper('market')->__('No select')),
			array('value'=>'Arial', 'label'=>Mage::helper('market')->__('Arial')),
			array('value'=>'Arial Black', 'label'=>Mage::helper('market')->__('Arial-black')),
			array('value'=>'Courier New', 'label'=>Mage::helper('market')->__('Courier New')),
			array('value'=>'Georgia', 'label'=>Mage::helper('market')->__('Georgia')),
			array('value'=>'Impact', 'label'=>Mage::helper('market')->__('Impact')),
			array('value'=>'Lucida Console', 'label'=>Mage::helper('market')->__('Lucida-console')),
			array('value'=>'Lucida Grande', 'label'=>Mage::helper('market')->__('Lucida-grande')),
			array('value'=>'Palatino', 'label'=>Mage::helper('market')->__('Palatino')),
			array('value'=>'Tahoma', 'label'=>Mage::helper('market')->__('Tahoma')),
			array('value'=>'Times New Roman', 'label'=>Mage::helper('market')->__('Times New Roman')),	
			array('value'=>'Trebuchet', 'label'=>Mage::helper('market')->__('Trebuchet')),	
			array('value'=>'Verdana', 'label'=>Mage::helper('market')->__('Verdana'))		
		);
	}
}
