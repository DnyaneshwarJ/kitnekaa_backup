<?php
/**
 * Displays the tabs on the left side
 * @category    Bobcares
 * @package     Bobcares_Quote2Sales
 */

class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
        parent::__construct();
        $this->setId('adminhtml_quote_view_tabs');
        $this->setDestElementId('adminhtml_quote_view');
        $this->setTitle(Mage::helper('quote2sales')->__('Quotes View'));
  }
}