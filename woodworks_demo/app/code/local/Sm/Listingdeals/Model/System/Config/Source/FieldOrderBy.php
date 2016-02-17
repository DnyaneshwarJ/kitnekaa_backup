<?php

/*------------------------------------------------------------------------
 # SM Listing Deals- Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Listingdeals_Model_System_Config_Source_FieldOrderBy
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'name', 'label' => Mage::helper('listingdeals')->__('Name')),
            array('value' => 'entity_id', 'label' => Mage::helper('listingdeals')->__('Id')),
            array('value' => 'position', 'label' => Mage::helper('listingdeals')->__('Position')),
            array('value' => 'created_at', 'label' => Mage::helper('listingdeals')->__('Date Created')),
            array('value' => 'price', 'label' => Mage::helper('listingdeals')->__('Price')),
            array('value' => 'lastest_product', 'label' => Mage::helper('listingdeals')->__('Lastest Product')),
            array('value' => 'top_rating', 'label' => Mage::helper('listingdeals')->__('Top Rating')),
            array('value' => 'most_reviewed', 'label' => Mage::helper('listingdeals')->__('Most Reviews')),
            array('value' => 'most_viewed', 'label' => Mage::helper('listingdeals')->__('Most Viewed')),
            array('value' => 'best_sales', 'label' => Mage::helper('listingdeals')->__('Most Selling')),
        );
    }
}
