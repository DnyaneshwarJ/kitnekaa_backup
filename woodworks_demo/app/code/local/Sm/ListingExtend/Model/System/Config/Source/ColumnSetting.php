<?php

/*------------------------------------------------------------------------
 # SM ListingExtend - Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_ListingExtend_Model_System_Config_Source_ColumnSetting
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => Mage::helper('listingextend')->__('1')),
            array('value' => 2, 'label' => Mage::helper('listingextend')->__('2')),
            array('value' => 3, 'label' => Mage::helper('listingextend')->__('3')),
            array('value' => 4, 'label' => Mage::helper('listingextend')->__('4')),
            array('value' => 5, 'label' => Mage::helper('listingextend')->__('5')),
            array('value' => 6, 'label' => Mage::helper('listingextend')->__('6'))

        );
    }
}
