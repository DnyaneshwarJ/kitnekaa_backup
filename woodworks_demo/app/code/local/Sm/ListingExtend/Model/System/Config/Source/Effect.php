<?php

/*------------------------------------------------------------------------
 # SM ListingExtend - Version 1.0.0
 # Copyright (c) 2015 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_ListingExtend_Model_System_Config_Source_Effect
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'none', 'label' => Mage::helper('listingextend')->__('None')),
            array('value' => 'fadeIn', 'label' => Mage::helper('listingextend')->__('Fade In')),
            array('value' => 'zoomIn', 'label' => Mage::helper('listingextend')->__('Zoom In')),
            array('value' => 'zoomOut', 'label' => Mage::helper('listingextend')->__('Zoom Out')),
            array('value' => 'slideLeft', 'label' => Mage::helper('listingextend')->__('Slide Left')),
            array('value' => 'slideRight', 'label' => Mage::helper('listingextend')->__('Slide Right')),
            array('value' => 'slideTop', 'label' => Mage::helper('listingextend')->__('Slide Top')),
            array('value' => 'slideBottom', 'label' => Mage::helper('listingextend')->__('Slide Bottom')),
            array('value' => 'flip', 'label' => Mage::helper('listingextend')->__('Flip')),
            array('value' => 'flipInX', 'label' => Mage::helper('listingextend')->__('Flip in horizontal')),
            array('value' => 'flipInY', 'label' => Mage::helper('listingextend')->__('Flip in vertical')),
            array('value' => 'bounceIn', 'label' => Mage::helper('listingextend')->__('Bounce In')),
            array('value' => 'bounceInUp', 'label' => Mage::helper('listingextend')->__('Bounce In Up')),
            array('value' => 'bounceInDown', 'label' => Mage::helper('listingextend')->__('Bounce In Down')),
            array('value' => 'pageTop', 'label' => Mage::helper('listingextend')->__('Page Top')),
            array('value' => 'pageBottom', 'label' => Mage::helper('listingextend')->__('Page Bottom')),
            array('value' => 'starwars', 'label' => Mage::helper('listingextend')->__('Star Wars')),
        );
    }
}
