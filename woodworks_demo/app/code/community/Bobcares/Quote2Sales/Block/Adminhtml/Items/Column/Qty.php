<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Items_Column_Qty extends Mage_Adminhtml_Block_Sales_Items_Column_Default
{
	public function getItem()
    {     if ($this->_getData('item') instanceof Mage_Sales_Model_Quote_Item) {
            return $this->_getData('item');
        } else {
            return $this->_getData('item')->getOrderItem();
        }
    }
}
?>
