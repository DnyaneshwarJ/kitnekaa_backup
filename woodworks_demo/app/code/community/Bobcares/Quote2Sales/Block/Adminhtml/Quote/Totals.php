<?php
/**
 * Quote Block for Viewing a quote's totals
 *
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_Totals extends Mage_Adminhtml_Block_Sales_Totals//Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * Initialize quote totals array
     *
     * @return Mage_Sales_Block_Quote_Totals
     */

   protected function _initTotals()
    {
        $source = $this->getSource();
        $totals = $source->getTotals(); 
        $this->_totals = array(); 
        foreach ($totals as $total){
        	$strong = false; if ($total->getCode() == "grand_total") $strong = true;
        	$this->_totals[$total->getCode()] = new Varien_Object(array(
        			'code'  => $total->getCode(),
        			'value' => $total->getValue(),
        			'label' => $total->getTitle(),
        			"strong" => $strong
        	));
        	
        }
        return $this; 
    }    	
    	public function getSource(){
    		return Mage::helper("quote2sales")->getQuote();
    	}
    	
	  public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->displayPrices(
                $this->getSource(),
                $total->getBaseValue(),
                $total->getValue()
            );
        }
        return $total->getValue();
    }
    /*
     * Displays the prices of each total
     */
   /* public function displayPrices($quote, $basePrice, $price, $strong = false, $separator = '<br/>'){
    	$helper = Mage::helper("quote2sales");

        if ($quote && $helper->isCurrencyDifferent()) {
            $res = '<strong>';
            $res.= $helper->formatBasePrice($basePrice);
            $res.= '</strong>'.$separator;
            $res.= '['.$helper->formatPrice($price).']';
        } elseif ($quote) {
            $res = $helper->formatPrice($price);
            if ($strong) {
                $res = '<strong>'.$res.'</strong>';
            }
        } else {
            $res = Mage::app()->getStore()->formatPrice($price);
            if ($strong) {
                $res = '<strong>'.$res.'</strong>';
            }
        }
        return $res;
    }*/

    /*
     * Added by Dnyanesh
     */
    public function displayPrices($quote, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        $helper = Mage::helper("quote2sales");

        if ($quote && $helper->isCurrencyDifferent()) {
            $res = '<strong>';
            $res .= $helper->formatBasePrice($basePrice);
            $res .= '</strong>' . $separator;
            $res .= '[' . Mage::helper('core')->currency($price, true, false) . ']';
        } elseif ($quote) {
            $res = Mage::helper('core')->currency($price, true, false);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        } else {
            $res = Mage::app()->getStore()->formatPrice($price);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
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
        return $this->helper("quote2sales")->formatPrice($price, $addBrackets); 
    }
    
    protected function formatBasePrice($price, $addBrackets = false){
    //    return Mage::getModel("sales/order")->formatBasePrice($price);
        return $this->helper("quote2sales")->formatBasePrice($price, $addBrackets);
    }
    }