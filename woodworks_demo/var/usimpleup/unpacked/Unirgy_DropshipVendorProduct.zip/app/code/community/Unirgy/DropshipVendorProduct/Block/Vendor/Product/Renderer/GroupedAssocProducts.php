<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_GroupedAssocProducts extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _prepareCollection()
    {
        $allowProductTypes = array();
        $allowProductTypeNodes = Mage::getConfig()
            ->getNode('global/catalog/product/type/grouped/allow_product_types')->children();
        foreach ($allowProductTypeNodes as $type) {
            $allowProductTypes[] = $type->getName();
        }

        $collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('type_id', $allowProductTypes);

        if ($this->getIsReadonly() === true) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
        }
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $joinCond = '{{table}}.vendor_id='.intval($vendor->getId());
        $joinCond .= ' and {{table}}.is_attribute=1';
        $collection->joinTable(
            'udropship/vendor_product_assoc', 'product_id=entity_id',
            array('microsite_vendor'=>'vendor_id'),
            $joinCond
        );
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    protected function _toHtml()
    {
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $res = Mage_Core_Block_Template::_toHtml();
        if (!$this->getSkipSerializer()) {
            $ser = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
            $ser->initSerializerBlock($this, 'getSelectedGroupedProducts', 'links[grouped]', 'products_grouped');
            $ser->addColumnInputName('qty','position');
            $res = $res.$ser->toHtml();
        }
        Mage::helper('udropship/catalog')->setDesignStore();
        return $res;
    }
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
}