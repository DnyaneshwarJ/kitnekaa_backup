<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Autocomplete queries list
 */
class Mage_Wishlist_Block_Autosuggest extends Mage_Core_Block_Abstract {
	protected $_suggestData = null;
	protected function _toHtml() {
		$html = '';
		
		if (! $this->_beforeToHtml ()) {
			return $html;
		}
		
		$suggestData = $this->getSuggestData ();
		if (! ($count = count ( $suggestData ))) {
			return $html;
		}
		
		$count --;
		
		$html = '<ul style="width:365px;"><li style="display:none"></li>';
		foreach ( $suggestData as $index => $item ) {
			if ($index == 0) {
				$item ['row_class'] .= ' first';
			}
			
			if ($index == $count) {
				$item ['row_class'] .= ' last';
			}
			
			$html .= '<li title="' . $this->escapeHtml ( $item ['product_name'] ) . '" id="' . $this->escapeHtml ( $item ['product_id'] ) . '" class="' . $item ['row_class'] . '">' . $this->escapeHtml ( $item ['product_name'] ) . '</li>';
		}
		
		$html .= '</ul>';
		
		return $html;
	}
	public function getSuggestData() {
		$query_text = $this->helper ( 'wishlist' )->getQueryText ();
		$this->_suggestData = $this->getWishList ()->getProductList ( $query_text );
		
		return $this->_suggestData;
	}
	public function getWishList() {
		
		return Mage::getSingleton ( 'wishlist/wishlist' );
	}
	/*
	 *
	 */
}
