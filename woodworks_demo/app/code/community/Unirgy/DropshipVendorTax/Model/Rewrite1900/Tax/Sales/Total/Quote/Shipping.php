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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Model to calculate shipping tax
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team
 */
class Unirgy_DropshipVendorTax_Model_Rewrite1900_Tax_Sales_Total_Quote_Shipping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Tax calculation model
     *
     * @var Mage_Tax_Model_Calculation
     */
    protected $_calculator = null;

    /**
     * Tax configuration object
     *
     * @var Mage_Tax_Model_Config
     */
    protected $_config = null;

    /**
     * Tax helper instance
     *
     * @var Mage_Tax_Helper_Data|null
     */
    protected $_helper = null;

    /**
     * Flag which is initialized when collect method is started and catalog prices include tax.
     * It is used for checking if store tax and customer tax requests are similar
     *
     * @var bool
     */
    protected $_areTaxRequestsSimilar = false;

    /**
     * Request which can be used for tax rate calculation
     *
     * @var Varien_Object
     */
    protected $_storeTaxRequest = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setCode('shipping');
        $this->_calculator  = Mage::getSingleton('tax/calculation');
        $this->_helper      = Mage::helper('tax');
        $this->_config      = Mage::getSingleton('tax/config');
    }

    /**
     * Collect totals information about shipping
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $udsDetails = $address->getUdropshipShippingDetails();
        if (!is_array($udsDetails)) {
            $udsDetails = Zend_Json::decode($udsDetails);
        }
        if (Mage::helper('udropship')->isUdsplitActive()) {
            $_udsDetails = @$udsDetails['methods'];
            if (is_array($_udsDetails)) {
                $dKey = key($_udsDetails);
                if (is_string($dKey)) {
                    $_udsDetails = current($_udsDetails);
                    $_udsDetails = @$_udsDetails['vendors'];
                }
            }
        } else {
            $_udsDetails = false;
            $uMethod = explode('_', $address->getShippingMethod(), 2);
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
            if (is_array($udsDetails) && !empty($udsDetails['methods'][$uMethodCode]['vendors'])
                && is_array($udsDetails['methods'][$uMethodCode]['vendors'])
            ) {
                $_udsDetails = $udsDetails['methods'][$uMethodCode]['vendors'];
            }
        }
        if (!is_array($_udsDetails)) {
            $_udsDetails = array();
        }

        $calc               = $this->_calculator;
        $store              = $address->getQuote()->getStore();
        $storeTaxRequest    = $calc->getRateOriginRequest($store);
        $addressTaxRequest  = $calc->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );

        $_udsDetailsEmpty = false;
        if (empty($_udsDetails)) {
            $_udsDetailsEmpty = true;
            $items = $address->getAllNonNominalItems();
            $vIds = array();
            $iHlp = Mage::helper('udropship/item');
            foreach ($items as $item) {
                $vIds[$iHlp->getUdropshipVendor($item)] = $iHlp->getUdropshipVendor($item);
            }
            $__baseShipping = $_baseShippingFull = $address->getBaseShippingAmount();
            $_baseShippingRound = 0;
            $vIds = array_filter(array_values($vIds));
            if (count($vIds)>0) {
                $__baseShipping = round($_baseShippingFull/count($vIds),2);
                $_baseShippingRound = $_baseShippingFull-$__baseShipping*count($vIds);
            }
            foreach ($vIds as $idx=>$vId) {
                $_udsDetails[$vId]['price'] = $__baseShipping;
                if ($idx==count($vIds)-1) {
                    $_udsDetails[$vId]['price'] += $_baseShippingRound;
                }
            }
        }

        if ($address->getAddressType()=='billing') {
            $_udsDetails = array();
        }

        $isPriceInclTax = false;
        $shipTaxTotals = array();
        foreach ($_udsDetails as $vId => &$_udsDetail) {

        $shippingTaxClass = $this->_config->getShippingTaxClass($store);
        $storeTaxRequest->setProductClassId($shippingTaxClass);
        $addressTaxRequest->setProductClassId($shippingTaxClass);
        Mage::helper('udtax')->setVendorClassId($storeTaxRequest, $vId);
        Mage::helper('udtax')->setVendorClassId($addressTaxRequest, $vId);

        $priceIncludesTax = $this->_config->shippingPriceIncludesTax($store);
        if ($priceIncludesTax) {
            if ($this->_helper->isCrossBorderTradeEnabled($store)) {
                $this->_areTaxRequestsSimilar = true;
            } else {
                $this->_areTaxRequestsSimilar =
                    $this->_calculator->compareRequests($storeTaxRequest, $addressTaxRequest);
            }
        }
        $this->_areTaxRequestsSimilar = false;

        $_vShipBaseAmount = $_udsDetail['price'];
        $_vShipAmount = $address->getQuote()->getStore()->convertPrice($_vShipBaseAmount, false);

        $shipping           = $taxShipping = $_vShipAmount;
        $baseShipping       = $baseTaxShipping = $_vShipBaseAmount;
        $rate               = $calc->getRate($addressTaxRequest);
        if ($priceIncludesTax) {
            if ($this->_areTaxRequestsSimilar) {
                $tax            = $this->_round($calc->calcTaxAmount($shipping, $rate, true, false), $rate, true);
                $baseTax        = $this->_round(
                    $calc->calcTaxAmount($baseShipping, $rate, true, false), $rate, true, 'base');
                $taxShipping    = $shipping;
                $baseTaxShipping = $baseShipping;
                $shipping       = $shipping - $tax;
                $baseShipping   = $baseShipping - $baseTax;
                $taxable        = $taxShipping;
                $baseTaxable    = $baseTaxShipping;
                $isPriceInclTax = true;
                $address->setTotalAmount('shipping', $shipping);
                $address->setBaseTotalAmount('shipping', $baseShipping);
            } else {
                $storeRate      = $calc->getStoreRate($addressTaxRequest, $store);
                $storeTax       = $calc->calcTaxAmount($shipping, $storeRate, true, false);
                $baseStoreTax   = $calc->calcTaxAmount($baseShipping, $storeRate, true, false);
                $shipping       = $calc->round($shipping - $storeTax);
                $baseShipping   = $calc->round($baseShipping - $baseStoreTax);
                $tax            = $this->_round($calc->calcTaxAmount($shipping, $rate, false, false), $rate, true);
                $baseTax        = $this->_round(
                    $calc->calcTaxAmount($baseShipping, $rate, false, false), $rate, true, 'base');
                $taxShipping    = $shipping + $tax;
                $baseTaxShipping = $baseShipping + $baseTax;
                $taxable        = $taxShipping;
                $baseTaxable    = $baseTaxShipping;
                $isPriceInclTax = true;
                //$address->setTotalAmount('shipping', $shipping);
                //$address->setBaseTotalAmount('shipping', $baseShipping);
            }
        } else {
            $appliedRates = $calc->getAppliedRates($addressTaxRequest);
            $taxes = array();
            $baseTaxes = array();
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $taxId = $appliedRate['id'];
                $taxes[] = $this->_round($calc->calcTaxAmount($shipping, $taxRate, false, false), $taxId, false);
                $baseTaxes[] = $this->_round(
                    $calc->calcTaxAmount($baseShipping, $taxRate, false, false), $taxId, false, 'base');
            }
            $tax            = array_sum($taxes);
            $baseTax        = array_sum($baseTaxes);
            $taxShipping    = $shipping + $tax;
            $baseTaxShipping = $baseShipping + $baseTax;
            $taxable        = $shipping;
            $baseTaxable    = $baseShipping;
            $isPriceInclTax = false;
            //address->setTotalAmount('shipping', $shipping);
            //$address->setBaseTotalAmount('shipping', $baseShipping);
        }

        $shipTaxTotals[$vId] = array(
            'shipping' => $shipping,
            'baseShipping' => $baseShipping,
            'taxShipping' => $taxShipping,
            'baseTaxShipping' => $baseTaxShipping,
            'taxable' => $taxable,
            'baseTaxable' => $baseTaxable,
            'isPriceInclTax' => $isPriceInclTax,
        );
        $_udsDetail['price_incl_tax'] = $baseTaxShipping;

        }
        unset($_udsDetail);
        if ($_udsDetailsEmpty) {
            $_udsDetails = array();
        }

        if (Mage::helper('udropship')->isUdsplitActive()) {
            $udsDetails['methods'] = $_udsDetails;
        } else {
            $uMethod = explode('_', $address->getShippingMethod(), 2);
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
            if (is_array($udsDetails) && !empty($udsDetails['methods'][$uMethodCode]['vendors'])
                && is_array($udsDetails['methods'][$uMethodCode]['vendors'])
            ) {
                $udsDetails['methods'][$uMethodCode]['vendors'] = $_udsDetails;
            }
        }

        $address->setUdropshipShippingDetails(Zend_Json::encode($udsDetails));

        $address->setVendorShippingTaxDetails($shipTaxTotals);

        $shipping = $baseShipping = $taxShipping = $baseTaxShipping = $taxable = $baseTaxable = 0;
        foreach ($shipTaxTotals as $shipTaxTotal) {
            $shipping += $shipTaxTotal['shipping'];
            $baseShipping += $shipTaxTotal['baseShipping'];
            $taxShipping += $shipTaxTotal['taxShipping'];
            $baseTaxShipping += $shipTaxTotal['baseTaxShipping'];
            $taxable += $shipTaxTotal['taxable'];
            $baseTaxable += $shipTaxTotal['baseTaxable'];
        }

        $address->setTotalAmount('shipping', $shipping);
        $address->setBaseTotalAmount('shipping', $baseShipping);
        $address->setShippingInclTax($taxShipping);
        $address->setBaseShippingInclTax($baseTaxShipping);
        $address->setShippingTaxable($taxable);
        $address->setBaseShippingTaxable($baseTaxable);
        $address->setIsShippingInclTax($isPriceInclTax);
        if ($this->_config->discountTax($store)) {
            $address->setShippingAmountForDiscount($taxShipping);
            $address->setBaseShippingAmountForDiscount($baseTaxShipping);
        }
        return $this;
    }

    /**
     * Round price based on tax rounding settings
     *
     * @param float $price
     * @param string $rate
     * @param bool $direction
     * @param string $type
     * @return float
     */
    protected function _round($price, $rate, $direction, $type = 'regular')
    {
        if (!$price) {
            return $this->_calculator->round($price);
        }

        $deltas = $this->_address->getRoundingDeltas();
        $key = $type.$direction;
        $rate = (string) $rate;
        $delta = isset($deltas[$key][$rate]) ? $deltas[$key][$rate] : 0;
        return $this->_calculator->round($price+$delta);
    }

    /**
     * Get request for fetching store tax rate
     *
     * @deprecated after 1.4.0.0
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Varien_Object
     */
    protected function _getStoreTaxRequest($address)
    {
        if (is_null($this->_storeTaxRequest)) {
            $this->_storeTaxRequest = $this->_calculator->getRateOriginRequest($address->getQuote()->getStore());
        }
        return $this->_storeTaxRequest;
    }

    /**
     * Get request for fetching address tax rate
     *
     * @deprecated after 1.4.0.0
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Varien_Object
     */
    protected function _getAddressTaxRequest($address)
    {
        $addressTaxRequest = $this->_calculator->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $address->getQuote()->getStore()
        );
        return $addressTaxRequest;
    }

    /**
     * Check if we need subtract store tax amount from shipping
     *
     * @deprecated after 1.4.0.0
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
     */
    protected function _needSubtractShippingTax($address)
    {
        $store = $address->getQuote()->getStore();
        if ($this->_config->shippingPriceIncludesTax($store) || $this->_config->getNeedUseShippingExcludeTax()) {
            return true;
        }
        return false;
    }

    /**
     * Calculate shipping price without store tax
     *
     * @deprecated after 1.4.0.0
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _processShippingAmount($address)
    {
    }
}
