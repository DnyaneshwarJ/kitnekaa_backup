<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Tax_Total extends Mage_Sales_Model_Quote_Address_Total_Tax
{
    protected function _setRequestVendor($store, $item, $request)
    {
        if (Mage::getStoreConfig('udropship/vendor/tax_by_vendor', $store)) {
            $request->setVendor(Mage::helper('udropship')->getVendor($item->getProduct()));
        }
    }

    /**
    * @version 1.3.2.2
    * @param Mage_Sales_Model_Quote_Address $address
    */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $store = $address->getQuote()->getStore();

        $address->setTaxAmount(0);
        $address->setBaseTaxAmount(0);
        //$address->setShippingTaxAmount(0);
        //$address->setBaseShippingTaxAmount(0);
        $address->setAppliedTaxes(array());

        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();

        $taxCalculationModel = Mage::getSingleton('tax/calculation');
        /* @var $taxCalculationModel Mage_Tax_Model_Calculation */
        $request = $taxCalculationModel->getRateRequest($address, $address->getQuote()->getBillingAddress(), $custTaxClassId, $store);

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent
             */
            if ($item->getParentItemId()) {
                continue;
            }
            /**
             * We calculate parent tax amount as sum of children's tax amounts
             */

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_setRequestVendor($store, $child, $request); //UDROPSHIP

                    $discountBefore = $item->getDiscountAmount();
                    $baseDiscountBefore = $item->getBaseDiscountAmount();

                    $rate = $taxCalculationModel->getRate($request->setProductClassId($child->getProduct()->getTaxClassId()));
                    $child->setTaxPercent($rate);
                    $child->calcTaxAmount();

                    if ($discountBefore != $item->getDiscountAmount()) {
                        $address->setDiscountAmount($address->getDiscountAmount()+($item->getDiscountAmount()-$discountBefore));
                        $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+($item->getBaseDiscountAmount()-$baseDiscountBefore));

                        $address->setGrandTotal($address->getGrandTotal() - ($item->getDiscountAmount()-$discountBefore));
                        $address->setBaseGrandTotal($address->getBaseGrandTotal() - ($item->getBaseDiscountAmount()-$baseDiscountBefore));
                    }

                    $this->_saveAppliedTaxes(
                       $address,
                       $taxCalculationModel->getAppliedRates($request),
                       $child->getTaxAmount(),
                       $child->getBaseTaxAmount(),
                       $rate
                    );
                }
                $address->setTaxAmount($address->getTaxAmount() + $item->getTaxAmount());
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $item->getBaseTaxAmount());
            }
            else {
                $this->_setRequestVendor($store, $item, $request); //UDROPSHIP

                $discountBefore = $item->getDiscountAmount();
                $baseDiscountBefore = $item->getBaseDiscountAmount();

                $rate = $taxCalculationModel->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));

                $item->setTaxPercent($rate);
                $item->calcTaxAmount();

                if ($discountBefore != $item->getDiscountAmount()) {
                    $address->setDiscountAmount($address->getDiscountAmount()+($item->getDiscountAmount()-$discountBefore));
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+($item->getBaseDiscountAmount()-$baseDiscountBefore));

                    $address->setGrandTotal($address->getGrandTotal() - ($item->getDiscountAmount()-$discountBefore));
                    $address->setBaseGrandTotal($address->getBaseGrandTotal() - ($item->getBaseDiscountAmount()-$baseDiscountBefore));
                }

                $address->setTaxAmount($address->getTaxAmount() + $item->getTaxAmount());
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $item->getBaseTaxAmount());

                $applied = $taxCalculationModel->getAppliedRates($request);
                $this->_saveAppliedTaxes(
                   $address,
                   $applied,
                   $item->getTaxAmount(),
                   $item->getBaseTaxAmount(),
                   $rate
                );
            }
        }

        $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);

        $shippingTax      = 0;
        $shippingBaseTax  = 0;

        if ($shippingTaxClass) {
            if ($rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass))) {
                if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
                    $shippingTax    = $address->getShippingAmount() * $rate/100;
                    $shippingBaseTax= $address->getBaseShippingAmount() * $rate/100;
                } else {
                    $shippingTax    = $address->getShippingTaxAmount();
                    $shippingBaseTax= $address->getBaseShippingTaxAmount();
                }

                $shippingTax    = $store->roundPrice($shippingTax);
                $shippingBaseTax= $store->roundPrice($shippingBaseTax);

                $address->setTaxAmount($address->getTaxAmount() + $shippingTax);
                $address->setBaseTaxAmount($address->getBaseTaxAmount() + $shippingBaseTax);

                $this->_saveAppliedTaxes(
                    $address,
                    $taxCalculationModel->getAppliedRates($request),
                    $shippingTax,
                    $shippingBaseTax,
                    $rate
                );
            }
        }

        if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
            $address->setShippingTaxAmount($shippingTax);
            $address->setBaseShippingTaxAmount($shippingBaseTax);
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseTaxAmount());

        return $this;
    }

}