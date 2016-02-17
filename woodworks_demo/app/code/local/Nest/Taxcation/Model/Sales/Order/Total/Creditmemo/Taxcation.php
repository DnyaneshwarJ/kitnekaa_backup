<?php

class Nest_Taxcation_Model_Sales_Order_Total_Creditmemo_Taxcation extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{

    /**
     * Collect credit memo total
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Nest_Taxcation_Model_Sales_Order_Total_Creditmemo_Taxcation
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $NestTaxAmount = $order->getNestTaxTotalAmount();
        if($NestTaxAmount){
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $NestTaxAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $NestTaxAmount);
        }

        return $this;
    }

}