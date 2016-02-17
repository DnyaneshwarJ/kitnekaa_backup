<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_GridRenderer_Countries extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (($rows = $row->getRows()) && is_array($rows)) {
            $countryNames = array();
            foreach ($rows as $row) {
                $_name = Mage::app()->getLocale()->getCountryTranslation($row['country_id']);
                $countryNames[] = $_name ? $_name : $row['country_id'];
            }
            $countryNames = implode(', ', $countryNames);
            if (empty($countryNames)) {
                $countryNames = $this->escapeHtml($countryNames);
            }
            return $countryNames;
        }
        return null;
    }
}