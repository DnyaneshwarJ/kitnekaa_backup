<?php

class Nest_Taxcation_Block_Sales_Order_Taxcation extends Mage_Core_Block_Template
{

    /**
     * Get order store object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Initialize fee totals
     *
     * @return Nest_Taxcation_Block_Sales_Order_Taxcation
     */
    public function initTotals()
    {
        if ((float) $this->getOrder()->getNestTaxTotalAmount()) {
            $source = $this->getSource();
            $value  = $source->getNestTaxTotalAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'Taxcation',
                'strong' => false,
                //'label'  => Mage::helper('taxcation')->__('Taxcation'),
                'label'  => $this->getOrder()->getNestTaxName(),
                'value'  => $value
            )));
        }

        return $this;
    }
}