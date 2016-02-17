<?php
class Nest_Taxcation_Model_Sales_Quote_Address_Total_Taxcation extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
 
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
 
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
 
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        
        $quote = $address->getQuote();
  
        //your business logic

        //loop throuth cart and collect required data
        $cartArray = array();
        $total_tax = 0;

        foreach ($address->getAllItems() as $items) {

            $tax_amount =  Nest_Taxcation_Model_Taxcation::calNestTaxAmount($items->getNestTaxPercent(), $items->getProduct()->getPrice(), $items->getQty());
            if( ($tax_amount != $items->getNestTaxAmount()) && ($items->getId() != '') ) {
                $update_nest_tax_in_quote_item_query = "update sales_flat_quote_item set nest_tax_amount = ".$tax_amount."  where item_id = ".$items->getId();
                $writeConnection->query($update_nest_tax_in_quote_item_query);
            }
            $total_tax += $tax_amount;
        }

        $tax_name = 'Vat';
            
        $address->setNestTaxName($tax_name);
        $address->setNestTaxTotalAmount($total_tax);
             
        $quote->setNestTaxTotalAmount($total_tax);

        $address->setGrandTotal($address->getGrandTotal() + $address->getNestTaxTotalAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getNestTaxTotalAmount());
        
    }
 
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amt = $address->getNestTaxTotalAmount();
        $address->addTotal(array(
                'code' =>$this->getCode(),
                //'title'=>Mage::helper('taxcation')->__('Taxcation'),
                'title'=>$address->getNestTaxName(),
                'value'=> $amt
        ));
        return $this;
    }
}