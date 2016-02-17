<?php
class Unirgy_DropshipVendorProduct_Model_ProductType_Simple15
    extends Mage_Catalog_Model_Product_Type_Simple
{
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        if ($buyRequest->getcpid()) {
            $confParent = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($buyRequest->getcpid());
            $product->addCustomOption('cpid', $buyRequest->getcpid(), $confParent);
        }
        return array($product);
    }

    public function hasConfigurableProductParentId($product = null)
    {
        if ($this->getProduct($product)->getCustomOption('cpid')) {
            return true;
        }
        return false;
    }

    public function getConfigurableProductParentId($product = null)
    {
        if ($this->getProduct($product)->getCustomOption('cpid')) {
            return $this->getProduct($product)->getCustomOption('cpid')->getValue();
        }
        return null;
    }

    public function getConfigurableProductParent($product = null)
    {
        if ($this->getProduct($product)->getCustomOption('cpid')) {
            return $this->getProduct($product)->getCustomOption('cpid')->getProduct();
        }
        return null;
    }

    public function getOrderOptions($product = null)
    {
        $optionArr = parent::getOrderOptions($product);
        if ($this->hasConfigurableProductParentId($product)) {
            $attributes = $this->getConfigurableProductParent($product)
                ->getTypeInstance(true)
                ->getUsedProductAttributes($this->getConfigurableProductParent($product));
            foreach($attributes as $attribute) {
                $optionArr['options'][] = array(
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->getProduct($product)->getAttributeText($attribute->getAttributeCode()),
                    'option_id' => $attribute->getId(),
                );
            }
        }
        return $optionArr;
    }
}
