<?php

class UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Quote_Grid extends Kitnekaa_Quote2SalesCustom_Block_Adminhtml_Quote_Grid
{
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter("grand_total", array("gt" => 0))
            ->setOrder('created_at', 'desc');
        if (Mage::helper('udquote2sale')->getVendorId() && Mage::helper('udquote2sale')->isSeller())
        {
            $collection->addFieldToFilter("vendor_id",array("eq"=>Mage::helper('udquote2sale')->getVendorId()));
        }
        $this->addRequestIdAttributeToCollection($collection);
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    protected function _prepareColumns()
    {

        if (!Mage::helper('udquote2sale')->getVendorId()) {
            $this->addColumnAfter('vendor_id', array(
                'header' => Mage::helper('quote2sales')->__('Vendor'),
                'width' => '100px',
                'index' => 'vendor_id',
                'renderer'=>new UnirgyCustom_DropshipQuote2sale_Block_Adminhtml_Renderer_VendorName()
            ), 'quote_request_by');
        }

        return parent::_prepareColumns();
    }
}
