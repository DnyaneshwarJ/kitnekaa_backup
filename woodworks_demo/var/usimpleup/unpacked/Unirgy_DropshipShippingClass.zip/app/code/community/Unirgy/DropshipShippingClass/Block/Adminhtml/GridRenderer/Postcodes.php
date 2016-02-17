<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_GridRenderer_Postcodes extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (($rows = $row->getRows()) && is_array($rows)) {
            $postCodes = array();
            foreach ($rows as $row) {
                $postCodes[] = $row['country_id'].': '.($row['postcode'] ? $row['postcode'] : '*');
            }
            return implode("<br />", $postCodes);
        }
        return null;
    }
}