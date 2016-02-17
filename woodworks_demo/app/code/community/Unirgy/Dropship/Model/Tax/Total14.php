<?php

class Unirgy_Dropship_Model_Tax_Total14 extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    protected function _setRequestVendor($store, $item, $request)
    {
        if (Mage::getStoreConfig('udropship/vendor/tax_by_vendor', $store)) {
            $request->setVendor(Mage::helper('udropship')->getVendor($item->getUdropshipVendor()));
        }
    }

    protected function _unitBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $store = $address->getQuote() ? $address->getQuote()->getStoreId() : null;

        $items  = $address->getAllItems();
        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_setRequestVendor($store, $child, $taxRateRequest); //UDROPSHIP

                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $this->_calcUnitTaxAmount($child, $rate);

                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());

                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate);
                }
                $this->_recalculateParent($item);
            }
            else {
                $this->_setRequestVendor($store, $item, $taxRateRequest); //UDROPSHIP

                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);

                $this->_calcUnitTaxAmount($item, $rate);

                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());

                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate);
            }
        }
        return $this;
    }

    protected function _rowBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $store = $address->getQuote() ? $address->getQuote()->getStoreId() : null;

        $items  = $address->getAllItems();
        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_setRequestVendor($store, $child, $taxRateRequest); //UDROPSHIP

                    $rate = $this->_calculator->getRate(
                        $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId())
                    );
                    $this->_calcRowTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());

                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate);
                }
                $this->_recalculateParent($item);
            }
            else {
                $this->_setRequestVendor($store, $item, $taxRateRequest); //UDROPSHIP

                $rate = $this->_calculator->getRate(
                    $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
                $this->_calcRowTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());

                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate);
            }
        }
        return $this;
    }

    protected function _totalBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items      = $address->getAllItems();
        $store      = $address->getQuote()->getStore();
        $taxGroups  = array();

        foreach ($items as $item) {
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_setRequestVendor($store, $child, $taxRateRequest); //UDROPSHIP

                    $rate = $this->_calculator->getRate(
                        $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId())
                    );
                    $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_aggregateTaxPerRate($child, $rate, $taxGroups);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_setRequestVendor($store, $item, $taxRateRequest); //UDROPSHIP

                $rate = $this->_calculator->getRate(
                    $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
                $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
            }
        }

        $inclTax = $this->_usePriceIncludeTax($store);
        foreach ($taxGroups as $rateKey => $data) {
            $rate = (float) $rateKey;
            $totalTax = $this->_calculator->calcTaxAmount(array_sum($data['totals']), $rate, $inclTax);
            $baseTotalTax = $this->_calculator->calcTaxAmount(array_sum($data['base_totals']), $rate, $inclTax);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTax, $baseTotalTax, $rate);
        }
        return $this;
    }
}
