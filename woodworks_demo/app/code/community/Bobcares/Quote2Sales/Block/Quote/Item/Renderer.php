<?php
class Bobcares_Quote2Sales_Block_Quote_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer//Mage_Core_Block_Template
{
	/**
	 * Get item configurable product
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getConfigurableProduct()
	{
		if ($option = $this->getItem()->getOptionByCode('product_type')) {
			return $option->getProduct();
		}
		return $this->getProduct();
	}
	
}
