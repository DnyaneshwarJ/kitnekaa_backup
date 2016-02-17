<?php 

class Nest_Taxcation_Model_Observer
{
    public function checkoutCartProductAddAfter($observer)
    {
    	$getTaxRateArray = array();

        $item = $observer->getEvent()->getQuoteItem();

        //Get customer default shiping addess
        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
		if ($customerAddressId){
			$address = Mage::getSingleton('customer/address')->load($customerAddressId);
			$getTaxRateArray['to'] = $customerRegion = $address->getRegion();
		}

		//get product shipping origin region
		//$getTaxRateArray['from'] = '';

		//get product category
		$product      = Mage::getSingleton('catalog/product')->load($item->getProductId());
		$categoryIds  = $product->getCategoryIds();
		$category     = Mage::getSingleton('catalog/category')->load($categoryIds[0]);
		$getTaxRateArray['category'] = $category->getName();
        
        //get tax rate for this item
        $nestTaxArray = Nest_Taxcation_Model_Taxcation::getNestTaxRate($getTaxRateArray);

        if(!empty($nestTaxArray))
        {
        	$tax_amount =  Nest_Taxcation_Model_Taxcation::calNestTaxAmount($nestTaxArray['discountRate'], $product->getPrice(), $item->getQty());

        	$item->setData('nest_tax_percent', $nestTaxArray['discountRate']);
        	$item->setData('nest_tax_amount',  $tax_amount);
            $item->save();
        }
        return $this;
    }


    public function checkoutShippingAddressSaveAfter($observer)
    {
        $getTaxRateArray = array();

        //echo "<pre>"; print_r($item = $observer->getQuoteItem()); exit;

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $getTaxRateArray['to'] = $quote->getShippingAddress()->getRegion();

        foreach ($quote->getAllItems() as  $item) {

            $price = $item->getProduct()->getPrice();

            //get product category
            $product      = Mage::getSingleton('catalog/product')->load($item->getProductId());
            $categoryIds  = $product->getCategoryIds();
            $category     = Mage::getSingleton('catalog/category')->load($categoryIds[0]);
            $getTaxRateArray['category'] = $category->getName();

            //get product shipping origin region
            //$getTaxRateArray['from'] = '';

            //get tax rate for this item
            $nestTaxArray = Nest_Taxcation_Model_Taxcation::getNestTaxRate($getTaxRateArray);

            if(!empty($nestTaxArray))
            {
                $tax_amount =  Nest_Taxcation_Model_Taxcation::calNestTaxAmount($nestTaxArray['discountRate'], $product->getPrice(), $item->getQty());
                
                $item->setData('nest_tax_percent', $nestTaxArray['discountRate']);
                $item->setData('nest_tax_amount',  $tax_amount);

                $item->save();

            }
        }
        return $this;
    }

}