<?php 

class Kitnekaa_Shoppinglist_Block_Adminhtml_Modifylist_Shoplist
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface{
   /**
     * Set the template for the block
     *
     */
    public function _construct()
    { 
        parent::_construct();
        $this->setTemplate('shoppinglist/modifylist/shoppinglist.phtml');
    }
   /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Edit Shoppinglist');
    }
   /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('modifiedshoplist');
    }
   /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    public function getAfter()
    {
        return 'tags';
    }
}