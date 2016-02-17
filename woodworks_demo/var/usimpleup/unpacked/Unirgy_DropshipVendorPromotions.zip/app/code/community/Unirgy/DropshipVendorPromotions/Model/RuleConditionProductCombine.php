<?php

class Unirgy_DropshipVendorPromotions_Model_RuleConditionProductCombine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object)
    {
        if ($object->getUdropshipVendor()!=$this->getRule()->getUdropshipVendor() && $this->getRule()->getUdropshipVendor()) {
            return false;
        }
        if (!$this->getConditions()) {
            return true;
        }
        return parent::validate($object);
    }
}