<?php
class Bobcares_Quote2Sales_Block_Quote_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable{
	/**
	 * Get product thumbnail image
	 *
	 * @return Mage_Catalog_Model_Product_Image
	 */
	public function getProductThumbnail()
	{
		$product = $this->getChildProduct();
		if (!$product || !$product->getData('thumbnail')
		|| ($product->getData('thumbnail') == 'no_selection')){
		//|| (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) == self::USE_PARENT_IMAGE)) {
			$product = $this->getProduct();
		}
		return $this->helper('catalog/image')->init($product, 'thumbnail');
	}
}
