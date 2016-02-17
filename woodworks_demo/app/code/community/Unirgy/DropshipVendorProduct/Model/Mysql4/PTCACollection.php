<?php

class Unirgy_DropshipVendorProduct_Model_Mysql4_PTCACollection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Attribute_Collection
{
    public function setProductFilter($product)
    {
        $this->_product = $product;
        $this->addFieldToFilter('main_table.product_id', $product->getId());
        if ($product->getIsProductListFlag() && !$product->getPTCAFilteredFlag()) {
            $product->setPTCAFilteredFlag(true);
            $this->getSelect()
                ->join(
                    array('lsl' => $this->getTable('catalog/product_super_link')),
                    'main_table.product_id=lsl.parent_id',
                    array())
                ->join(
                    array('av' => $this->getTable('catalog/product').'_int'),
                    'main_table.attribute_id=av.attribute_id and lsl.product_id=av.entity_id and av.store_id=0',
                    array())
                ->group('main_table.product_super_attribute_id');

            if ($product->getIsProductListFlag() == 1) {
                $this->getSelect()
                    ->columns(array('count(distinct av.value) as lpr_cnt'))
                    ->where('main_table.identify_image>0 and main_table.identify_image is not null')
                    ->having('lpr_cnt>1')->limit(1);
            }

            $product->setPTCAColSel(clone $this->getSelect());
        }
        return $this;
    }
    public function myAfterLoad()
    {
        Varien_Profiler::start('TTT1:'.__METHOD__);
        $this->_addProductAttributes();
        Varien_Profiler::stop('TTT1:'.__METHOD__);
        Varien_Profiler::start('TTT2:'.__METHOD__);
        $this->_addAssociatedProductFilters();
        Varien_Profiler::stop('TTT2:'.__METHOD__);
        Varien_Profiler::start('TTT3:'.__METHOD__);
        $this->_loadLabels();
        Varien_Profiler::stop('TTT3:'.__METHOD__);
        Varien_Profiler::start('TTT4:'.__METHOD__);
        $this->_loadPrices();
        Varien_Profiler::stop('TTT4:'.__METHOD__);
        return $this;
    }
    protected function _afterLoad()
    {
        $res = $this->getFlag('unirgy_skip_afterload')
            ? Mage_Core_Model_Mysql4_Collection_Abstract::_afterLoad()
            : parent::_afterLoad();

        foreach ($this as $item) {
            if (!is_array($prices = $item->getPrices())) $item->setPrices(array());
        }

        return $res;
    }
}
