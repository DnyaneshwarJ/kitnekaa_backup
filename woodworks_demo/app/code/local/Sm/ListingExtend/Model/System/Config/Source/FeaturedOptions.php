<?php

/*------------------------------------------------------------------------
 # SM ListingExtend - Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_ListingExtend_Model_System_Config_Source_FeaturedOptions
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('listingextend')->__('Show')),
            array('value' => 1, 'label' => Mage::helper('listingextend')->__('Hide')),
            array('value' => 2, 'label' => Mage::helper('listingextend')->__('Only')),
        );
    }
}
