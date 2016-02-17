<?php

class Nest_Taxcation_Model_Sales_Order_Total_Invoice_Taxcation extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    /**
     * Collect invoice total
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Nest_Taxcation_Model_Sales_Order_Total_Invoice_Taxcation
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $NestTaxAmount = $order->getNestTaxTotalAmount();
        if($NestTaxAmount){
            $invoice->setGrandTotal($invoice->getGrandTotal() + $NestTaxAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $NestTaxAmount);
        }

        return $this;
    }

}