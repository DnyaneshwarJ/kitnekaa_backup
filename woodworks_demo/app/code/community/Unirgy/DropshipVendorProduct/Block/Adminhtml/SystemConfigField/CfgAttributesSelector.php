<?php

class Unirgy_DropshipVendorProduct_Block_Adminhtml_SystemConfigField_CfgAttributesSelector extends Unirgy_Dropship_Block_Adminhtml_SystemConfigFormField_FieldContainer
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udprod/system/cfg_attributes_selector.phtml');
        }
    }
    public function prepareIdSuffix($id)
    {
        return md5($id);
    }
    
}
