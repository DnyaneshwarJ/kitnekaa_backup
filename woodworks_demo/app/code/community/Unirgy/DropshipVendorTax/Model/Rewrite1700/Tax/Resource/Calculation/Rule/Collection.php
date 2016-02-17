<?php

class Unirgy_DropshipVendorTax_Model_Rewrite1700_Tax_Resource_Calculation_Rule_Collection extends Mage_Tax_Model_Resource_Calculation_Rule_Collection
{
    public function setClassTypeFilter($type, $id)
    {
        switch ($type) {
            case Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT:
                $field = 'cd.product_tax_class_id';
                break;
            case Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER:
                $field = 'cd.customer_tax_class_id';
                break;
            case Unirgy_DropshipVendorTax_Model_Source::TAX_CLASS_TYPE_VENDOR:
                $field = 'cd.vendor_tax_class_id';
                break;
            default:
                Mage::throwException('Invalid type supplied');
        }

        $this->joinCalculationData('cd');
        $this->addFieldToFilter($field, $id);
        return $this;
    }
}