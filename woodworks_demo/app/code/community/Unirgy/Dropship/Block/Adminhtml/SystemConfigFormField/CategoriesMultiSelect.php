<?php

class Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_CategoriesMultiSelect extends Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_CategoriesSelect
{
    protected function _getTypeBlockClass()
    {
        return Mage::getConfig()->getBlockClassName('udropship/categoriesMultiSelect');
    }

}