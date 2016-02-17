<?php

class UnirgyCustom_DropshipQuote2sale_Model_Adminhtml_Quote_Create extends Bobcares_Quote2Sales_Model_Adminhtml_Quote_Create
{

    /**
     * Add multiple products to current order quote
     *
     * @param   array $products
     * @return  Mage_Adminhtml_Model_Sales_Order_Create|Exception
     */
    public function addProducts(array $products)
    {
        foreach ($products as $productId => $config) {
            if (Mage::helper('udquote2sale')->getVendorId()) {
                $config['udropship_vendor'] = Mage::helper('udquote2sale')->getVendorId();
            }
            $config['qty'] = isset($config['qty']) ? (float)$config['qty'] : 1;
            try {
                $this->addProduct($productId, $config);
            } catch (Mage_Core_Exception $e) {
                $this->getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                return $e;
            }
        }
        return $this;
    }

}