<?php

class Unirgy_DropshipVendorPromotions_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const UDPROMO_STATUS_ACTIVE = 1;
    const UDPROMO_STATUS_INACTIVE = 0;

    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udpromo');

        $options = array();

        switch ($this->getPath()) {

            case 'statuses':
                $options = array(
                    self::UDPROMO_STATUS_ACTIVE  => Mage::helper('udropship')->__('Active'),
                    self::UDPROMO_STATUS_INACTIVE => Mage::helper('udropship')->__('Inactive'),
                );
                break;

            default:
                Mage::throwException(Mage::helper('udropship')->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>Mage::helper('udropship')->__('* Please select')) + $options;
        }

        return $options;
    }
}
