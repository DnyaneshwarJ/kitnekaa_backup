<?php

/*------------------------------------------------------------------------
 # SM Listing Deals- Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Listingdeals_Model_System_Config_Source_ListEffect
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'slideLeft', 'label' => Mage::helper('listingdeals')->__('Slide Left')),
            array('value' => 'slideRight', 'label' => Mage::helper('listingdeals')->__('Slide Right')),
            array('value' => 'zoomOut', 'label' => Mage::helper('listingdeals')->__('Zoom Out')),
            array('value' => 'zoomIn', 'label' => Mage::helper('listingdeals')->__('Zoom In')),
            array('value' => 'flip', 'label' => Mage::helper('listingdeals')->__('Flip')),
            array('value' => 'flipInX', 'label' => Mage::helper('listingdeals')->__('Fip in Vertical')),
            array('value' => 'starwars', 'label' => Mage::helper('listingdeals')->__('Star Wars')),
            array('value' => 'flipInY', 'label' => Mage::helper('listingdeals')->__('Flip in Horizontal')),
            array('value' => 'bounceIn', 'label' => Mage::helper('listingdeals')->__('Bounce In')),
            array('value' => 'fadeIn', 'label' => Mage::helper('listingdeals')->__('Fade In')),
            array('value' => 'pageTop', 'label' => Mage::helper('listingdeals')->__('Page Top')),
        );
    }
}
