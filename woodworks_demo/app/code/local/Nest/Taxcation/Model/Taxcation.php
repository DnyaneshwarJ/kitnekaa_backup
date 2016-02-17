<?php
class Nest_Taxcation_Model_Taxcation extends Mage_Core_Model_Abstract {

	/**
	 * Provide avaliable options as a value/label array
	 *
	 *
	 * @return select options array
	 **/
	public function toOptionArray()
	{
		return array(
			array('value' => 'Test', 'label' => 'Test'),
			array('value' => 'Production', 'label' => 'Production')
		);
	}


	/**
     * Check if taxcation can be apply
     *
     * @static
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
     */
    public static function canApply($address)
    {
        //Put here your business logic to check if Tax should be applied or not
		$flag = false;

        

        $flag = true;
        return $flag;
    }

    /**
     * GET NEST TAX RATE for item added to cart 
     *
     * 
     * @param Nest_Taxcation_Model_Observer::checkoutCartProductAddAfter(Item_added_to_cart)
     * @return NEST TAX RATE AND NEST TAX AMOUNT
     */
    public function getNestTaxRate($item)
    {
    	$_mode 			= Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_mode');
		//$_accountNumber 	= Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_account_number');
		//$_accessToken 	= Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_access_token');	
		//$_secreatKey	 	= Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_secreat_key');	

		if($_mode == 'Test') {
		 	$_url	 = Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_test_url');
		} else {
			$_url	 = Mage::getStoreConfig('taxcation/taxcation_groups/nest_taxcation_production_url');
		}

		//$post_data = 'fromState='.$item['from'].'&toState='.$item['to'].'&category='.$item['category'];

		//test purpose
		$post_data = 'fromState=MH&toState=MH&category=C1';

		//make curl call
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $_url);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		curl_setopt( $ch, CURLOPT_POST, count( $post_data ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_data );

		// Run cURL request
		$nest_taxcation_hit = curl_exec( $ch );
		$nest_taxcation_dataArray = json_decode($nest_taxcation_hit, true);

		// Close cURL request
		curl_close( $ch );
		return $nest_taxcation_dataArray;

    }

    /**
     * Calculates Nest Tax Amount  
     *
     * 
     * @param productPrice, productQty, NestTaxPercent
     * @return NEST TAX RATE AND NEST TAX AMOUNT
     */
    public function calNestTaxAmount($nestTaxPercent, $productPrice, $productQty)
    {
    	$tax_rate 	= $nestTaxPercent / 100 ;
    	$tax_amount =  ( ($productQty * $productPrice) * $tax_rate );

    	return $tax_amount;
    }


}