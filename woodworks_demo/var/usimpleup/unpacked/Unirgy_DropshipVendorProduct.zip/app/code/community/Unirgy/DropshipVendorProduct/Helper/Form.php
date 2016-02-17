<?php

class Unirgy_DropshipVendorProduct_Helper_Form extends Mage_Core_Helper_Abstract
{
    public function getSkinUrl($path)
    {
        return Mage::getDesign()->getSkinUrl($path);
    }

    public function getSkinBaseUrl()
    {
        return Mage::getDesign()->getSkinBaseUrl();
    }

    public function isIE6()
    {
        return preg_match('/MSIE [1-6]\./i', Mage::app()->getRequest()->getServer('HTTP_USER_AGENT'));
    }

    public function isIE7()
    {
        return preg_match('/MSIE [1-7]\./i', Mage::app()->getRequest()->getServer('HTTP_USER_AGENT'));
    }
    const MAX_QTY_VALUE = 99999999.9999;
    public function isQty($product)
    {
        return Mage::helper('cataloginventory')->isQty($product->getTypeId());
    }
    public function getStockItemField($field, $values)
    {
        $fieldDef = array();
        switch ($field) {
            case 'is_in_stock':
                $fieldDef = array(
                    'id'       => 'stock_data_is_in_stock',
                    'type'     => 'select',
                    'name'     => 'stock_data[is_in_stock]',
                    'label'    => Mage::helper('udropship')->__('Stock Status'),
                    'options'  => Mage::getSingleton('udprod/source')->setPath('stock_status')->toOptionHash(),
                    'value'    => @$values['stock_data']['is_in_stock']
                );
                break;
            case 'qty':
                $fieldDef = array(
                    'id'       => 'stock_data_qty',
                    'type'     => 'stock_data_qty',
                    'name'     => 'stock_data[qty]',
                    'label'    => Mage::helper('udropship')->__('Stock Qty'),
                    'value'    => @$values['stock_data']['qty']*1
                );
                break;
            case 'manage_stock':
                $fieldDef = array(
                    'id'       => 'stock_data_manage_stock',
                    'use_config_id' => 'stock_data_use_config_manage_stock',
                    'default_id' => 'stock_data_manage_stock_default',
                    'type'     => 'select',
                    'name'     => 'stock_data[manage_stock]',
                    'use_config_name'     => 'stock_data[use_config_manage_stock]',
                    'label'    => Mage::helper('udropship')->__('Manage Stock'),
                    'value'    => @$values['stock_data']['manage_stock']*1,
                    'use_config_value' => @$values['stock_data']['use_config_manage_stock']*1,
                    'values'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionArray(),
                    'renderer' => Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_useConfigElement'),
                    'vendor_use_custom_field' => 'is_udprod_manage_stock',
                    'vendor_field' => 'udprod_manage_stock',
                    'config_path' => Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK,
                );
                break;
            case 'backorders':
                $fieldDef = array(
                    'id'       => 'stock_data_backorders',
                    'use_config_id' => 'stock_data_use_config_backorders',
                    'default_id' => 'stock_data_backorders_default',
                    'type'     => 'select',
                    'name'     => 'stock_data[backorders]',
                    'use_config_name'     => 'stock_data[use_config_backorders]',
                    'label'    => Mage::helper('udropship')->__('Backorders'),
                    'value'    => @$values['stock_data']['backorders']*1,
                    'use_config_value' => @$values['stock_data']['use_config_backorders']*1,
                    'values'   => Mage::getSingleton('udprod/source')->setPath('udprod_backorders')->toOptionArray(),
                    'renderer' => Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_useConfigElement'),
                    'vendor_use_custom_field' => 'is_udprod_backorders',
                    'vendor_field' => 'udprod_backorders',
                    'config_path' => Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS,
                );
                break;
            case 'min_qty':
                $fieldDef = array(
                    'id'       => 'stock_data_min_qty',
                    'use_config_id' => 'stock_data_use_config_min_qty',
                    'default_id' => 'stock_data_min_qty_default',
                    'type'     => 'text',
                    'name'     => 'stock_data[min_qty]',
                    'use_config_name'     => 'stock_data[use_config_min_qty]',
                    'label'    => Mage::helper('udropship')->__('Qty for Item\'s Status to Become Out of Stock'),
                    'value'    => @$values['stock_data']['min_qty']*1,
                    'use_config_value' => @$values['stock_data']['use_config_min_qty']*1,
                    'renderer' => Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_useConfigElement'),
                    'vendor_use_custom_field' => 'is_udprod_min_qty',
                    'vendor_field' => 'udprod_min_qty',
                    'config_path' => Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY,
                );
                break;
            case 'min_sale_qty':
                $fieldDef = array(
                    'id'       => 'stock_data_min_sale_qty',
                    'use_config_id' => 'stock_data_use_config_min_sale_qty',
                    'default_id' => 'stock_data_min_sale_qty_default',
                    'type'     => 'text',
                    'name'     => 'stock_data[min_sale_qty]',
                    'use_config_name'     => 'stock_data[use_config_min_sale_qty]',
                    'label'    => Mage::helper('udropship')->__('Minimum Qty Allowed in Shopping Cart'),
                    'value'    => @$values['stock_data']['min_sale_qty']*1,
                    'use_config_value' => @$values['stock_data']['use_config_min_sale_qty']*1,
                    'renderer' => Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_useConfigElement'),
                    'vendor_use_custom_field' => 'is_udprod_min_sale_qty',
                    'vendor_field' => 'udprod_min_sale_qty',
                    'config_path' => Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_SALE_QTY,
                );
                break;
            case 'max_sale_qty':
                $fieldDef = array(
                    'id'       => 'stock_data_max_sale_qty',
                    'use_config_id' => 'stock_data_use_config_max_sale_qty',
                    'default_id' => 'stock_data_max_sale_qty_default',
                    'type'     => 'text',
                    'name'     => 'stock_data[max_sale_qty]',
                    'use_config_name'     => 'stock_data[use_config_max_sale_qty]',
                    'label'    => Mage::helper('udropship')->__('Maximum Qty Allowed in Shopping Cart'),
                    'value'    => @$values['stock_data']['max_sale_qty']*1,
                    'use_config_value' => @$values['stock_data']['use_config_max_sale_qty']*1,
                    'renderer' => Mage::app()->getLayout()->createBlock('udprod/vendor_product_renderer_useConfigElement'),
                    'vendor_use_custom_field' => 'is_udprod_max_sale_qty',
                    'vendor_field' => 'udprod_max_sale_qty',
                    'config_path' => Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY,
                );
                break;
        }
        return $fieldDef;
    }
    public function getSystemField($field, $values)
    {
        $fieldDef = array();
        switch ($field) {
            case 'product_categories':
                $fieldDef = array(
                    'id'       => 'product_categories',
                    'type'     => 'product_categories',
                    'name'     => 'category_ids',
                    'label'    => Mage::helper('udropship')->__('Categories'),
                    'value'    => @$values['product_categories'],
                );
                break;
            case 'product_websites':
                $fieldDef = array(
                    'id'       => 'product_websites',
                    'type'     => 'multiselect',
                    'name'     => 'website_ids',
                    'label'    => Mage::helper('udropship')->__('Websites'),
                    'value'    => @$values['product_websites'],
                    'values'   => Mage::getSingleton('udprod/source')->setPath('product_websites')->toOptionArray()
                );
                break;
        }
        return $fieldDef;
    }
    public function getAttributeField($attribute)
    {
        $fieldDef = array();
        if ($attribute && (!$attribute->hasIsVisible() || $attribute->getIsVisible())
            && ($inputType = $attribute->getFrontend()->getInputType())
        ) {
            $fieldType      = $inputType;
            if ($attribute->getAttributeCode()=='tier_price') $fieldType='tier_price';
            if ($attribute->getAttributeCode()=='group_price') $fieldType='group_price';
            $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
            if (!empty($rendererClass)) {
                $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
            }
            $fieldDef = array(
                'id'       => $attribute->getAttributeCode(),
                'type'     => $fieldType,
                'name'     => $attribute->getAttributeCode(),
                'label'    => $attribute->getFrontend()->getLabel(),
                'class'    => $attribute->getFrontend()->getClass(),
                'note'     => $attribute->getNote(),
                'input_renderer' => $rendererClass,
                'entity_attribute' => $attribute
            );
            if ($inputType == 'select') {
                $fieldDef['values'] = $attribute->getSource()->getAllOptions(true, true);
            } else if ($inputType == 'multiselect') {
                $fieldDef['values'] = $attribute->getSource()->getAllOptions(false, true);
                $fieldDef['can_be_empty'] = true;
            } else if ($inputType == 'date') {
                $fieldDef['image'] = $this->getSkinUrl('images/grid-cal.gif');
                $fieldDef['format'] = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            } else if ($inputType == 'multiline') {
                $fieldDef['line_count'] = $attribute->getMultilineCount();
            }
        }
        return $fieldDef;
    }
    public function getUdmultiField($field, $mvData)
    {
        $fieldDef = array();
        switch ($field) {
            case 'status':
                $fieldDef = array(
                    'id' => 'udmulti_status',
                    'type'     => 'select',
                    'name'     => 'udmulti[status]',
                    'label'    => Mage::helper('udropship')->__('Status'),
                    'options'   => Mage::getSingleton('udmulti/source')->setPath('vendor_product_status')->toOptionHash(),
                    'value'     => @$mvData['status']
                );
                break;
            case 'state':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $fieldDef = array(
                    'id' => 'udmulti_state',
                    'type'     => 'select',
                    'name'     => 'udmulti[state]',
                    'label'    => Mage::helper('udropship')->__('State (Condition)'),
                    'options'  => Mage::getSingleton('udmultiprice/source')->setPath('vendor_product_state')->toOptionHash(),
                    'value'    => @$mvData['state']
                );
                }
                break;
            case 'stock_qty':
                $v = @$mvData['stock_qty'];
                $fieldDef = array(
                    'id' => 'udmulti_stock_qty',
                    'type'     => 'text',
                    'name'     => 'udmulti[stock_qty]',
                    'label'    => Mage::helper('udropship')->__('Stock Qty'),
                    'value'    => null !== $v ? $v*1 : ''
                );
                break;
            case 'state_descr':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $fieldDef = array(
                    'id' => 'udmulti_state_descr',
                    'type'     => 'text',
                    'name'     => 'udmulti[state_descr]',
                    'label'    => Mage::helper('udropship')->__('State description'),
                    'value'    => @$mvData['state_descr']
                );
                }
                break;
            case 'vendor_title':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $fieldDef = array(
                    'id' => 'udmulti_vendor_title',
                    'type'     => 'text',
                    'name'     => 'udmulti[vendor_title]',
                    'label'    => Mage::helper('udropship')->__('Vendor Title'),
                    'value'    => @$mvData['vendor_title']
                );
                }
                break;
            case 'vendor_cost':
                $v = @$mvData['vendor_cost'];
                $fieldDef = array(
                    'id' => 'udmulti_vendor_cost',
                    'type'     => 'text',
                    'name'     => 'udmulti[vendor_cost]',
                    'label'    => Mage::helper('udropship')->__('Vendor Cost'),
                    'value'    => null !== $v ? $v*1 : ''
                );
                break;
            case 'vendor_price':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $v = @$mvData['vendor_price'];
                $fieldDef = array(
                    'id' => 'udmulti_vendor_price',
                    'type'     => 'text',
                    'name'     => 'udmulti[vendor_price]',
                    'label'    => Mage::helper('udropship')->__('Vendor Price'),
                    'value'    => null !== $v ? $v*1 : ''
                );
                }
                break;
            case 'group_price':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                    $v = @$mvData['group_price'];
                    $fieldDef = array(
                        'id' => 'udmulti_group_price',
                        'type'     => 'udmulti_group_price',
                        'name'     => 'udmulti[group_price]',
                        'input_renderer' => Mage::getConfig()->getBlockClassName('udmulti/vendor_productAttribute_form_groupPrice'),
                        'label'    => Mage::helper('udropship')->__('Group Price'),
                        'value'    => !empty($v) && is_array($v) ? $v : array()
                    );
                }
                break;
            case 'tier_price':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                    $v = @$mvData['tier_price'];
                    $fieldDef = array(
                        'id' => 'udmulti_tier_price',
                        'type'     => 'udmulti_tier_price',
                        'name'     => 'udmulti[tier_price]',
                        'input_renderer' => Mage::getConfig()->getBlockClassName('udmulti/vendor_productAttribute_form_tierPrice'),
                        'label'    => Mage::helper('udropship')->__('Tier Price'),
                        'value'    => !empty($v) && is_array($v) ? $v : array()
                    );
                }
                break;
            case 'special_price':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $v = @$mvData['special_price'];
                $fieldDef = array(
                    'id' => 'udmulti_special_price',
                    'type'     => 'text',
                    'name'     => 'udmulti[special_price]',
                    'label'    => Mage::helper('udropship')->__('Vendor Special Price'),
                    'value'    => null !== $v ? $v*1 : ''
                );
                }
                break;
            case 'special_from_date':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $fieldDef = array(
                    'id' => 'udmulti_special_from_date',
                    'type'     => 'date',
                    'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                    'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                    'name'     => 'udmulti[special_from_date]',
                    'label'    => Mage::helper('udropship')->__('Vendor Special From Date'),
                    'value'    => @$mvData['special_from_date']
                );
                }
                break;
            case 'special_to_date':
                if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
                $fieldDef = array(
                    'id' => 'udmulti_special_to_date',
                    'type'     => 'date',
                    'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                    'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                    'name'     => 'udmulti[special_to_date]',
                    'label'    => Mage::helper('udropship')->__('Vendor Special To Date'),
                    'value'    => @$mvData['special_to_date']
                );
                }
                break;
            case 'vendor_sku':
                $fieldDef = array(
                    'id' => 'udmulti_vendor_sku',
                    'type'     => 'text',
                    'name'     => 'udmulti[vendor_sku]',
                    'label'    => Mage::helper('udropship')->__('Vendor Sku'),
                    'value'    => @$mvData['vendor_sku']
                );
                break;
            case 'freeshipping':
                $fieldDef = array(
                    'id' => 'udmulti_freeshipping',
                    'type'     => 'select',
                    'name'     => 'udmulti[freeshipping]',
                    'label'    => Mage::helper('udropship')->__('Is Free Shipping'),
                    'options'  => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(),
                    'value'    => @$mvData['freeshipping']*1
                );
                break;
            case 'shipping_price':
                $fieldDef = array(
                    'id' => 'udmulti_shipping_price',
                    'type'     => 'text',
                    'name'     => 'udmulti[shipping_price]',
                    'label'    => Mage::helper('udropship')->__('Shipping Price'),
                    'value'    => @$mvData['shipping_price']
                );
                break;
            case 'backorders':
                $fieldDef = array(
                    'id' => 'udmulti_backorders',
                    'type'     => 'select',
                    'name'     => 'udmulti[backorders]',
                    'label'    => Mage::helper('udropship')->__('Vendor Backorders'),
                    'options'  => Mage::getSingleton('udmulti/source')->setPath('backorders')->toOptionHash(),
                    'value'    => @$mvData['backorders']*1
                );
                break;
        }
        return $fieldDef;
    }
}