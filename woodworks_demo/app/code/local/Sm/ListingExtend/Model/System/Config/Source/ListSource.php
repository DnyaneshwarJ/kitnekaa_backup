<?php

/*------------------------------------------------------------------------
 # SM ListingExtend - Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_ListingExtend_Model_System_Config_Source_ListSource
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'catalog', 'label' => Mage::helper('listingextend')->__('Catalog')),
            array('value' => 'ids', 'label' => Mage::helper('listingextend')->__('Product IDs to Exclude'))
        );
    }
}
