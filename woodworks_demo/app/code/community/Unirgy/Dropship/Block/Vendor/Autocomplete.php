<?php

class Unirgy_Dropship_Block_Vendor_Autocomplete extends Mage_Core_Block_Template
{
    protected $_vendorPrefix;
    
    public function setVendorPrefix($vendorPrefix)
    {
        $this->_vendorPrefix = $vendorPrefix;
        return $this;
    }
    public function getVendorPrefix()
    {
        return $this->_vendorPrefix;
    }
    
    public function getSuggestData()
    {
        $vendors = Mage::getModel('udropship/vendor')->getCollection()->setOrder('vendor_name', 'asc')->setPageSize(20);
        $vendors->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('vendor_name', 'vendor_id'))
            ->where('vendor_name like ?', $this->getVendorPrefix().'%');
        return $vendors;
    }
    
    protected function _toHtml()
    {
        $html = '<ul><li style="display:none"></li>';
        $sd = $this->getSuggestData();
        foreach ($sd as $index => $item) {
            $rowClass = $index%2?'odd':'even';
            if ($index == 0) {
                $rowClass .= ' first';
            }

            if ($index == $sd->count()) {
                $rowClass .= ' last';
            }

            $html .=  '<li style="margin: 0px; min-height: 1.3em" title="'.$item->getId().'" class="'.$rowClass.'">'
                .$this->htmlEscape($item->getVendorName())
                .($index == $sd->count() && $sd->getSize()>$sd->count() ? '<span>...</span></li>' : '</li>');
        }

        $html.= '</ul>';

        return $html;
    }
}