<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
class Unirgy_DropshipVacation_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const MODE_NOT_VACATION     = 0;
    const MODE_VACATION_NOTIFY  = 1;
    const MODE_VACATION_DISABLE = 2;
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udvacation');

        switch ($this->getPath()) {

        case 'vacation_mode':
            $options = array(
                0 => Mage::helper('udropship')->__('Not Vacation'),
                1 => Mage::helper('udropship')->__('Notify Customer On Availability'),
                2 => Mage::helper('udropship')->__('Disable Products'),
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