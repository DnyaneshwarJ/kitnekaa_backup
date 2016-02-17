<?php
/**
 * Integrationmethods Model
 *
 * @category    Model
 * @package     Gremlin_Ccavenue
 * @author      Junaid Bhura <info@gremlin.io>
 */

/**
 * Used in creating options for Integration Method config value selection
 */
class Gremlin_Ccavenue_Model_Integrationmethods {

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray() {
		return array(
			array( 'value' => 'redirect', 'label' => 'Redirect' ),
			array( 'value' => 'iframe', 'label' => 'IFRAME' ),
		);
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'redirect' => 'Redirect',
			'iframe' => 'Iframe',
		);
	}

}
