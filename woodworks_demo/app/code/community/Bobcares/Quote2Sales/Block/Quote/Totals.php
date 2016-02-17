<?php
/**
 * Block Functions needed by the totals section of the View template
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Quote_Totals extends Mage_Sales_Block_Order_Totals
{
    /**
     * Associated array of totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;
    protected $_order = null;

    /**
     * Initialize self totals and children blocks totals before html building
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _beforeToHtml()
    {
        $this->_initTotals();
        foreach ($this->getChild() as $child) {
            if (method_exists($child, 'initTotals')) {
                $child->initTotals();
            }
        }
        return parent::_beforeToHtml();
    }

    /**
     * Get order object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif (Mage::registry('current_quote')) {
                $this->_order = Mage::registry('current_quote');
            } elseif ($this->getParentBlock()->getQuote()) {
                $this->_order = $this->getParentBlock()->getQuote();
            }
        }
        return $this->_order;
    }

    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getSource()
    {
    	return $this->getQuote();
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = array(); 
        
        $this->_totals['subtotal'] = new Varien_Object(array(
            'code'  => 'subtotal',
            'value' => $source->getSubtotal(),
            'label' => $this->__('Subtotal')
        ));


        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((float) $source->getShippingAmount() || $source->getShippingDescription()))
        {
            $this->_totals['shipping'] = new Varien_Object(array(
                'code'  => 'shipping',
                'field' => 'shipping_amount',
                'value' => $this->getSource()->getShippingAmount(),
                'label' => $this->__('Shipping & Handling')
            ));
        }
		/**
         * Add setup fees
         */
  /*          $address = $this->getSource()->getShippingAddress();
            Mage::getModel("Setupfee_Calculation_Model_Quote_Address_Total_Shipping")->collect($address);
            $amount = $address->getShippingAmount();
	        if (!$source->getIsVirtual() && $amount)
	        {
	            $this->_totals['setupfee'] = new Varien_Object(array(
	                'code'  => 'setupfee',
	                'field' => 'setupfee_amount',
	                'value' => $amount,
	                'label' => $this->__('Setup fee')
	            ));
	        }
    */    
        /**
         * Add discount
         */$discount = 0; 
        $subtotal_with_discount = $this->getSource()->getSubtotal_with_discount();
        $subtotal = $this->getSource()->getSubtotal(); 
        if ($subtotal> $subtotal_with_discount) $discount = $subtotal_with_discount - $subtotal; 
        if (((float)$discount) != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = $this->__('Discount (%s)', $source->getDiscountDescription());
            } else {
                $discountLabel = $this->__('Discount');
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code'  => 'discount',
                'field' => 'discount_amount',
                'value' => $discount,
                'label' => $discountLabel
            ));
        }

        $this->_totals['grand_total'] = new Varien_Object(array(
            'code'  => 'grand_total',
            'field'  => 'grand_total',
            'strong'=> true,
            'value' => $source->getGrandTotal(),
            'label' => $this->__('Grand Total')
        ));

        /**
         * Base grandtotal
         */
        //if ($this->getQuote()->isCurrencyDifferent()) {
/*        $baseGrandTotal = $this->formatBasePrice($source->getBaseGrandTotal());
            $this->_totals['base_grandtotal'] = new Varien_Object(array(
                'code'  => 'base_grandtotal',
                'value' => $baseGrandTotal,
                'label' => $this->__('Grand Total to be Charged'),
                'is_formated' => true,
            ));
*/        //}
        
            
        return $this;
    }

    /**
     * Add new total to totals array after specific total or before last total by default
     *
     * @param   Varien_Object $total
     * @param   null|string|last|first $after
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function addTotal(Varien_Object $total, $after=null)
    {
        if ($after !== null && $after != 'last' && $after != 'first') {
            $totals = array();
            $added = false;
            foreach ($this->_totals as $code => $item) {
                $totals[$code] = $item;
                if ($code == $after) {
                    $added = true;
                    $totals[$total->getCode()] = $total;
                }
            }
            if (!$added) {
                $last = array_pop($totals);
                $totals[$total->getCode()] = $total;
                $totals[$last->getCode()] = $last;
            }
            $this->_totals = $totals;
        } elseif ($after=='last')  {
            $this->_totals[$total->getCode()] = $total;
        } elseif ($after=='first')  {
            $totals = array($total->getCode()=>$total);
            $this->_totals = array_merge($totals, $this->_totals);
        } else {
            $last = array_pop($this->_totals);
            $this->_totals[$total->getCode()] = $total;
            $this->_totals[$last->getCode()] = $last;
        }
        return $this;
    }

    /**
     * Add new total to totals array before specific total or after first total by default
     *
     * @param   Varien_Object $total
     * @param   null|string $after
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function addTotalBefore(Varien_Object $total, $before=null)
    {
        if ($before !== null) {
            if (!is_array($before)) {
                $before = array($before);
            }
            foreach ($before as $beforeTotals) {
                if (isset($this->_totals[$beforeTotals])) {
                    $totals = array();
                    foreach ($this->_totals as $code => $item) {
                        if ($code == $beforeTotals) {
                            $totals[$total->getCode()] = $total;
                        }
                        $totals[$code] = $item;
                    }
                    $this->_totals = $totals;
                    return $this;
                }
            }
        }
        $totals = array();
        $first = array_shift($this->_totals);
        $totals[$first->getCode()] = $first;
        $totals[$total->getCode()] = $total;
        foreach ($this->_totals as $code => $item) {
            $totals[$code] = $item;
        }
        $this->_totals = $totals;
        return $this;
    }

    /**
     * Get Total object by code
     *
     * @@return Varien_Object
     */
    public function getTotal($code)
    {
        if (isset($this->_totals[$code])) {
            return $this->_totals[$code];
        }
        return false;
    }

    /**
     * Delete total by specific
     *
     * @param   string $code
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function removeTotal($code)
    {
        unset($this->_totals[$code]);
        return $this;
    }

    /**
     * Apply sort orders to totals array.
     * Array should have next structure
     * array(
     *  $totalCode => $totalSortOrder
     * )
     *
     *
     * @param   array $order
     * @return  Mage_Sales_Block_Order_Totals
     */
    public function applySortOrder($order)
    {
        return $this;
    }

    /**
     * get totals array for visualization
     *
     * @return array
     */
    public function getTotals($area=null)
    {
    /*	$totals = array();
        if ($area === null) {
            $totals = $this->_totals;
        } else {
            $area = (string)$area;
            foreach ($this->_totals as $total) {
                $totalArea = (string) $total->getArea();
                if ($totalArea == $area) {
                    $totals[] = $total;
                }
            }
        }
        return $totals;
        */return $this->getQuote()->getTotals(); 
    }

    /**
     * Format total value based on quote currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->formatPrice($total->getValue());
        }
        return $total->getValue();
    }
	/**
     * Get formated price value including quote currency rate to  website currency
     *
     * @param   float $price
     * @param   bool  $addBrackets
     * @return  string
     */

    protected function formatPrice($price, $addBrackets = false)
    {
        //Added by Dnyanesh
        return Mage::helper('core')->currency($price, true, false);
        //$this->helper("quote2sales")->formatPrice($price, $addBrackets);
    }

    protected function formatBasePrice($price, $addBrackets = false){
    //    return Mage::getModel("sales/order")->formatBasePrice($price);
        return $this->helper("quote2sales")->formatBasePrice($price, $addBrackets); 
    }
}
