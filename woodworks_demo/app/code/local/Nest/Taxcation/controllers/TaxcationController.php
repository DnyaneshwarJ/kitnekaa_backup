<?php
class Nest_Taxcation_TaxcationController extends Mage_Core_Controller_Front_Action
{
	
	/**
	 * function to hit nest taxcation api and get tax rate 
	 *
	 *
	 * @return tax rate 
	 **/
	public function getnesttaxrateAction()
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


		$from 	= 'MH';
		$to 	= 'MH';
		$cat 	= 'C1';

		//format data
		$post_data = 'fromState='.$from.'&toState='.$to.'&category='.$cat;


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
		//echo 'vatsal'; exit;
		return $nest_taxcation_dataArray;
	}
}