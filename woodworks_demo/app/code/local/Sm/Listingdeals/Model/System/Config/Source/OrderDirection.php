<?php

/*------------------------------------------------------------------------
 # SM Listing Deals- Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Listingdeals_Model_System_Config_Source_OrderDirection
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'ASC', 'label' => Mage::helper('listingdeals')->__('Asc')),
            array('value' => 'DESC', 'label' => Mage::helper('listingdeals')->__('Desc'))
        );
    }
}
