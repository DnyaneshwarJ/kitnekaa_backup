<?php
/*------------------------------------------------------------------------
 # SM Market - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Market_Model_System_Config_Source_ListGoogleFont
{
	public function toOptionArray()
	{	
		return array(
			array('value'=>'', 'label'=>Mage::helper('market')->__('No select')),
			array('value'=>'Anton', 'label'=>Mage::helper('market')->__('Anton')),
			array('value'=>'Questrial', 'label'=>Mage::helper('market')->__('Questrial')),
			array('value'=>'Kameron', 'label'=>Mage::helper('market')->__('Kameron')),
			array('value'=>'Oswald', 'label'=>Mage::helper('market')->__('Oswald')),
			array('value'=>'Open Sans', 'label'=>Mage::helper('market')->__('Open Sans')),
			array('value'=>'BenchNine', 'label'=>Mage::helper('market')->__('BenchNine')),
			array('value'=>'Droid Sans', 'label'=>Mage::helper('market')->__('Droid Sans')),
			array('value'=>'Droid Serif', 'label'=>Mage::helper('market')->__('Droid Serif')),
			array('value'=>'PT Sans', 'label'=>Mage::helper('market')->__('PT Sans')),
			array('value'=>'Vollkorn', 'label'=>Mage::helper('market')->__('Vollkorn')),
			array('value'=>'Ubuntu', 'label'=>Mage::helper('market')->__('Ubuntu')),
			array('value'=>'Neucha', 'label'=>Mage::helper('market')->__('Neucha')),
			array('value'=>'Cuprum', 'label'=>Mage::helper('market')->__('Cuprum'))	
		);
	}
}
