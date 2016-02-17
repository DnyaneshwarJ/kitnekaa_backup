<?php

/*------------------------------------------------------------------------
 # SM ListingExtend - Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_ListingExtend_Model_System_Config_Source_OrderBy
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'name', 'label' => Mage::helper('listingextend')->__('Name')),
            array('value' => 'entity_id', 'label' => Mage::helper('listingextend')->__('Id')),
            array('value' => 'created_at', 'label' => Mage::helper('listingextend')->__('Date Created')),
            array('value' => 'price', 'label' => Mage::helper('listingextend')->__('Price')),
            array('value' => 'lastest_product', 'label' => Mage::helper('listingextend')->__('Lastest Product')),
            array('value' => 'top_rating', 'label' => Mage::helper('listingextend')->__('Top Rating')),
            array('value' => 'most_reviewed', 'label' => Mage::helper('listingextend')->__('Most Reviews')),
            array('value' => 'most_viewed', 'label' => Mage::helper('listingextend')->__('Most Viewed')),
            array('value' => 'best_sales', 'label' => Mage::helper('listingextend')->__('Most Selling')),
            array('value' => 'random', 'label' => Mage::helper('listingextend')->__('Random')),
        );
    }
}
