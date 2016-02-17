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

class Unirgy_Dropship_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    public function getRate($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return 0;
        }

        //UDROPSHIP
        if ($request->getCountryId()=='US'
            && ($v = $request->getVendor()) && $v->getCountryId()=='US' && (
                !$v->getTaxRegions() ||
                $v->getTaxRegions() && !in_array($request->getRegionId(), (array)$v->getTaxRegions())
            )
        ) {
            return 0;
        }

        $cacheKey = "{$request->getProductClassId()}|{$request->getCustomerClassId()}|{$request->getCountryId()}|{$request->getRegionId()}|{$request->getPostcode()}";

        if (!isset($this->_rateCache[$cacheKey])) {
            $this->unsRateValue();
            $this->unsCalculationProcess();
            $this->unsEventModuleId();
            Mage::dispatchEvent('tax_rate_data_fetch', array('request'=>$this));
            if (!$this->hasRateValue()) {
                $this->setCalculationProcess($this->_getResource()->getCalculationProcess($request));
                $this->setRateValue($this->_getResource()->getRate($request));
            } else {
                $this->setCalculationProcess($this->_formCalculationProcess());
            }
            $this->_rateCache[$cacheKey] = $this->getRateValue();
            $this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
        }
        return $this->_rateCache[$cacheKey];
    }
}