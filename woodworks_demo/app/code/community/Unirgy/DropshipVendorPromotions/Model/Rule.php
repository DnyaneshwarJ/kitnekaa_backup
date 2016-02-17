<?php

class Unirgy_DropshipVendorPromotions_Model_Rule extends Mage_SalesRule_Model_Rule
{
    public function getActionsInstance()
    {
        return Mage::getModel('udpromo/ruleConditionProductCombine');
    }
}