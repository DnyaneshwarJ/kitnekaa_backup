<?php

class Unirgy_DropshipVendorPromotions_Model_RuleConditionAddress extends Mage_SalesRule_Model_Rule_Condition_Address
{
    public function validate(Varien_Object $object)
    {
        $address = $object;
        $attr = $this->getAttribute();
        $ruleVid = $this->getRule()->getUdropshipVendor();
        if (in_array($attr, array('base_subtotal', 'weight', 'total_qty')) && $ruleVid) {
            $origTotal = $address->getData($attr);
            $vendorTotal = Mage::helper('udpromo')->getQuoteAddrTotal($address, $attr, $ruleVid);
            $address->setData($attr, $vendorTotal);
        }

        $valResult = parent::validate($address);

        if (in_array($attr, array('base_subtotal', 'weight', 'total_qty')) && $ruleVid && isset($origTotal)) {
            $address->setData($attr, $origTotal);
        }

        return $valResult;
    }
}