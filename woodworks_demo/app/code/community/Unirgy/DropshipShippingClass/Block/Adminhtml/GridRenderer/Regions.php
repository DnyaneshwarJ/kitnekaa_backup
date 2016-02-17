<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_GridRenderer_Regions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (($rows = $row->getRows()) && is_array($rows)) {
            $regionCodes = array();
            foreach ($rows as $row) {
                $regionCodes[] = $row['country_id'].': '.($row['region_id'] ? @implode(',', $row['region_codes']) : '*');
            }
            return implode("<br />", $regionCodes);
        }
        return null;
    }
}