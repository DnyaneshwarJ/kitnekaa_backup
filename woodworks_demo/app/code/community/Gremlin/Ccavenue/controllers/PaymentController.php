<?php
/**
 * Payment Controller
 *
 * @category    Controller
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

class Gremlin_Ccavenue_PaymentController extends Mage_Core_Controller_Front_Action {
	
	/**
	 * Prepares the request to send to CC Avenue
	 * 
	 * @return void
	 */
	public function prepareRequest() {
		// Retrieve order
		$ccavenue['order_id'] = Mage::getSingleton( 'checkout/session' )->getLastRealOrderId();
		$order = Mage::getModel( 'sales/order' )->loadByIncrementId( $ccavenue['order_id'] );

		// Check if we have an order
		if ( ! $order->getEntityId() ) {
			Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/failure', array( '_secure' => true ) );
			return;
		}
		
		// CC Avenue Values
		$ccavenue['merchant_id'] = Mage::getStoreConfig( 'payment/ccavenue/merchant_id' );
		$ccavenue['amount'] = round( $order->getGrandTotal(), 2 );
		$ccavenue['redirect_url'] = Mage::getBaseUrl() . 'ccavenue/payment/response';
		$ccavenue['cancel_url'] = Mage::getBaseUrl() . 'ccavenue/payment/cancel';
		$accessCode = Mage::getStoreConfig( 'payment/ccavenue/access_code' );
		$encryptionKey = Mage::getStoreConfig( 'payment/ccavenue/encryption_key' );
		if ( ! Mage::getStoreConfig( 'payment/ccavenue/test_mode' ) )
			$action = 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
		else
			$action = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
		$ccavenue['currency'] = $order->getOrderCurrencyCode();
		$ccavenue['language'] = strtoupper( substr( Mage::app()->getLocale()->getLocaleCode(), 0, 2 ) );

		// Retrieve order details
		$billingAddress = $order->getBillingAddress();
		$billingData = $billingAddress->getData();
		$shippingAddress = $order->getShippingAddress();
		if ( $shippingAddress )
			$shippingData = $shippingAddress->getData();
		
		$ccavenue['billing_name'] = $billingData['firstname'] . ' ' . $billingData['lastname'];
		$ccavenue['billing_address'] = trim( $billingAddress->street, ",.'()*&%^\"!" );
		$ccavenue['billing_city'] = $billingAddress->city;
		$ccavenue['billing_state'] = $billingAddress->region;
		$ccavenue['billing_zip'] = $billingAddress->postcode;
		$ccavenue['billing_country'] = Mage::getModel( 'directory/country' )->load( $billingAddress->country_id )->getName();
		$ccavenue['billing_tel'] = $billingAddress->telephone;
		$ccavenue['billing_email'] = $order->customer_email;
		if ( $shippingAddress ) {
			$ccavenue['delivery_name'] = $shippingData['firstname'] . ' ' . $shippingData['lastname'];
			$ccavenue['delivery_address'] = trim( $shippingAddress->street, ",.'()*&%^\"!" );
			$ccavenue['delivery_city'] = $shippingAddress->city;
			$ccavenue['delivery_state'] = $shippingAddress->region;
			$ccavenue['delivery_zip'] = $shippingAddress->postcode;
			$ccavenue['delivery_country'] = Mage::getModel( 'directory/country' )->load( $shippingAddress->country_id )->getName();
			$ccavenue['delivery_tel'] = $shippingAddress->telephone;
		}
		else {
			$ccavenue['delivery_name'] = '';
			$ccavenue['delivery_address'] = '';
			$ccavenue['delivery_city'] = '';
			$ccavenue['delivery_state'] = '';
			$ccavenue['delivery_zip'] = '';
			$ccavenue['delivery_country'] = '';
			$ccavenue['delivery_tel'] = '';
		}

		// Check for IFRAME method
		if ( Mage::getStoreConfig( 'payment/ccavenue/integration_method' ) == 'iframe' )
			$ccavenue['integration_type'] = 'iframe_normal';
		
		// Build data string
		$dataString = '';
		foreach ( $ccavenue as $key => $value ) {
			$dataString .= $key . '=' . urlencode( $value ) . '&';
		}
		$dataString = rtrim( $dataString, '&' );

		// Add additional data for template
		$ccavenue['encrypted_data'] = Mage::helper( 'ccavenue/crypto' )->encrypt( $dataString, $encryptionKey );
		$ccavenue['access_code'] = $accessCode;
		$ccavenue['action'] = $action;

		// Save values into redirect log
		$now = Mage::getModel('core/date')->timestamp( time() );
		Mage::getModel( 'ccavenue/ccavenueredirect' )
			->setOrderId( $ccavenue['order_id'] )
			->setMerchantId( $ccavenue['merchant_id'] )
			->setAmount( $ccavenue['amount'] )
			->setRedirectUrl( $ccavenue['redirect_url'] )
			->setCancelUrl( $ccavenue['cancel_url'] )
			->setCurrency( $ccavenue['currency'] )
			->setLanguage( $ccavenue['language'] )
			->setEncryptedData( $ccavenue['encrypted_data'] )
			->setAccessCode( $ccavenue['access_code'] )
			->setAction( $ccavenue['action'] )
			->setBillingName( $ccavenue['billing_name'] )
			->setBillingAddress( $ccavenue['billing_address'] )
			->setBillingCity( $ccavenue['billing_city'] )
			->setBillingState( $ccavenue['billing_state'] )
			->setBillingZip( $ccavenue['billing_zip'] )
			->setBillingCountry( $ccavenue['billing_country'] )
			->setBillingTel( $ccavenue['billing_tel'] )
			->setBillingEmail( $ccavenue['billing_email'] )
			->setDeliveryName( $ccavenue['delivery_name'] )
			->setDeliveryAddress( $ccavenue['delivery_address'] )
			->setDeliveryCity( $ccavenue['delivery_city'] )
			->setDeliveryState( $ccavenue['delivery_state'] )
			->setDeliveryZip( $ccavenue['delivery_zip'] )
			->setDeliveryCountry( $ccavenue['delivery_country'] )
			->setDeliveryTel( $ccavenue['delivery_tel'] )
			->setIpAddress( $_SERVER['REMOTE_ADDR'] )
			->setCreatedAt( date( 'Y-m-d H:i:s', $now ) )
			->save();

		// Add data to registry so it's accessible in the view file
		Mage::register( 'ccavenue', $ccavenue );
		
		// Render template layout
		$this->loadLayout();
		$this->getLayout()->getBlock( 'head' )->setTitle( $this->__( 'CC Avenue Payment' ) );
		$this->renderLayout();
	}

	/**
	 * Saves the logs in the database and
	 * redirects the user to CC Avenue
	 */
	public function redirectAction() {
		$this->prepareRequest();
	}

	/**
	 * Saves the logs in the database and
	 * loads the CC Avenue IFRAME
	 */
	public function detailsAction() {
		$this->prepareRequest();
	}

	/**
	 * Triggers when CC Avenue sends a response
	 */
	public function responseAction() {
		if ( $this->getRequest()->isPost() ) {
			// CC Avenue Values
			$workingKey = Mage::getStoreConfig( 'payment/ccavenue/encryption_key' );
			
			// CC Avenue Response
			$encResponse = $this->getRequest()->getPost( 'encResp' );
			$decryptedString = Mage::helper( 'ccavenue/crypto' )->decrypt( $encResponse, $workingKey );
			$decryptedValues = explode( '&', $decryptedString );

			// Build response array
			$responseArray = array();
			foreach ( $decryptedValues as $value ) {
				$values = explode( '=', $value );

				if ( isset( $values[0] ) && isset( $values[1] ) )
					$responseArray[ $values[0] ] = $values[1];
			}

			// Check for order status
			if ( ! isset( $responseArray['order_status'] ) || ! isset( $responseArray['order_id'] ) ) {
				// No order status found! What!
				$this->cancelOrder( Mage::helper( 'ccavenue' )->__( 'The payment was cancelled because the order could not be found at checkout.' ) );
				Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/failure', array( '_secure ' => true) );
			}

			// Save values into response log
			$now = Mage::getModel( 'core/date' )->timestamp( time() );
			Mage::getModel( 'ccavenue/ccavenueresponse' )
				->setOrderId( isset( $responseArray['order_id'] ) ? $responseArray['order_id'] : '' )
				->setTrackingId( isset( $responseArray['tracking_id'] ) ? $responseArray['tracking_id'] : '' )
				->setBankRefNo( isset( $responseArray['bank_ref_no'] ) ? $responseArray['bank_ref_no'] : '' )
				->setOrderStatus( isset( $responseArray['order_status'] ) ? $responseArray['order_status'] : '' )
				->setFailureMessage( isset( $responseArray['failure_message'] ) ? $responseArray['failure_message'] : '' )
				->setPaymentMode( isset( $responseArray['payment_mode'] ) ? $responseArray['payment_mode'] : '' )
				->setCardName( isset( $responseArray['card_name'] ) ? $responseArray['card_name'] : '' )
				->setStatusCode( isset( $responseArray['status_code'] ) ? $responseArray['status_code'] : '' )
				->setStatusMessage( isset( $responseArray['status_message'] ) ? $responseArray['status_message'] : '' )
				->setCurrency( isset( $responseArray['currency'] ) ? $responseArray['currency'] : '' )
				->setAmount( isset( $responseArray['amount'] ) ? $responseArray['amount'] : '' )
				->setBillingName( isset( $responseArray['billing_name'] ) ? $responseArray['billing_name'] : '' )
				->setBillingAddress( isset( $responseArray['billing_address'] ) ? $responseArray['billing_address'] : '' )
				->setBillingCity( isset( $responseArray['billing_city'] ) ? $responseArray['billing_city'] : '' )
				->setBillingState( isset( $responseArray['billing_state'] ) ? $responseArray['billing_state'] : '' )
				->setBillingZip( isset( $responseArray['billing_zip'] ) ? $responseArray['billing_zip'] : '' )
				->setBillingCountry( isset( $responseArray['billing_country'] ) ? $responseArray['billing_country'] : '' )
				->setBillingTel( isset( $responseArray['billing_tel'] ) ? $responseArray['billing_tel'] : '' )
				->setBillingEmail( isset( $responseArray['billing_email'] ) ? $responseArray['billing_email'] : '' )
				->setDeliveryName( isset( $responseArray['delivery_name'] ) ? $responseArray['delivery_name'] : '' )
				->setDeliveryAddress( isset( $responseArray['delivery_address'] ) ? $responseArray['delivery_address'] : '' )
				->setDeliveryCity( isset( $responseArray['delivery_city'] ) ? $responseArray['delivery_city'] : '' )
				->setDeliveryState( isset( $responseArray['delivery_state'] ) ? $responseArray['delivery_state'] : '' )
				->setDeliveryZip( isset( $responseArray['delivery_zip'] ) ? $responseArray['delivery_zip'] : '' )
				->setDeliveryCountry( isset( $responseArray['delivery_country'] ) ? $responseArray['delivery_country'] : '' )
				->setDeliveryTel( isset( $responseArray['delivery_tel'] ) ? $responseArray['delivery_tel'] : '' )
				->setMerchantParam1( isset( $responseArray['merchant_param1'] ) ? $responseArray['merchant_param1'] : '' )
				->setMerchantParam2( isset( $responseArray['merchant_param2'] ) ? $responseArray['merchant_param2'] : '' )
				->setMerchantParam3( isset( $responseArray['merchant_param3'] ) ? $responseArray['merchant_param3'] : '' )
				->setMerchantParam4( isset( $responseArray['merchant_param4'] ) ? $responseArray['merchant_param4'] : '' )
				->setMerchantParam5( isset( $responseArray['merchant_param5'] ) ? $responseArray['merchant_param5'] : '' )
				->setIpAddress( $_SERVER['REMOTE_ADDR'] )
				->setCreatedAt( date( 'Y-m-d H:i:s', $now ) )
				->save();

			// Check the order status
			switch ( $responseArray['order_status'] ) {
				// Success!
				case 'Success':
					// Payment was successful, so update the order's state, send order email and move to the success page
					$order = Mage::getModel( 'sales/order' );
					$order->loadByIncrementId( $responseArray['order_id'] );
					$order->setState( Mage_Sales_Model_Order::STATE_PROCESSING, true, Mage::helper( 'ccavenue' )->__( 'Payment successful at CC Avenue.' ) );
					
					$order->sendNewOrderEmail();
					$order->setEmailSent( true );
					
					$order->save();
				
					Mage::getSingleton( 'checkout/session' )->unsQuoteId();
					
					Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/success', array( '_secure' => true ) );
					
					break;

				// Unsuccessful
				case 'Aborted':
				case 'Failure':
				default:
					// CCAvenue has declined the payment, so cancel the order and redirect to fail page
					$this->cancelOrder( Mage::helper( 'ccavenue' )->__( 'CC Avenue declined the payment.' ) );
					Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/failure', array( '_secure' => true) );
					
					break;
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect( '' );
	}

	/**
	 * Triggered when a user cancels a payment at CC Avenue
	 * or when there was a proble with the payment
	 */
	public function cancelAction() {
		$this->cancelOrder( Mage::helper( 'ccavenue' )->__( 'The payment was cancelled at CC Avenue.' ) );
		Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/failure', array( '_secure' => true) );
	}
	
	/**
	 * Function to cancel an order with a message
	 * 
	 * @param  string $cancelMessage
	 * @return void
	 */
	public function cancelOrder( $cancelMessage = '' ) {
		if ( $cancelMessage == '' )
			$cancelMessage = Mage::helper( 'ccavenue' )->__( 'The payment was cancelled due to a problem.' );

		if ( Mage::getSingleton( 'checkout/session' )->getLastRealOrderId() ) {
			$order = Mage::getModel( 'sales/order' )->loadByIncrementId( Mage::getSingleton( 'checkout/session' )->getLastRealOrderId() );
			if ( $order->getId() ) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState( Mage_Sales_Model_Order::STATE_CANCELED, true, $cancelMessage )->save();
			}
		}
	}

}