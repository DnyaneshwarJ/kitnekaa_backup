<?php

class Unirgy_DropshipVendorPromotions_Block_Adminhtml_CategoryCheckboxesTree extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{
    public function getLoadTreeUrl($expanded=null)
    {
        $params = array('_current'=>true, 'id'=>null,'store'=>null);
        if ($expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('udpromo/vendor/categoriesJson', $params);
    }
}