<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product extends Mage_Core_Block_Template
{
    protected $_form;
    protected $_product;
    protected $_oldStoreId;
    protected $_unregUrlStore;
    protected $_oldFieldsetRenderer;
    protected $_oldFieldsetElementRenderer;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!Mage::registry('url_store')) {
            $this->_unregUrlStore = true;
            Mage::register('url_store', Mage::app()->getStore());
        }
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->_oldFieldsetRenderer = Varien_Data_Form::getFieldsetRenderer();
        $this->_oldFieldsetElementRenderer = Varien_Data_Form::getFieldsetElementRenderer();
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('udprod/vendor_product_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('udprod/vendor_product_renderer_fieldsetElement')
        );

        //Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');

        return $this;
    }

    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = array())
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        return parent::getUrl($route, $params);
    }

    protected function _afterToHtml($html)
    {
        /*
        Varien_Data_Form::setFieldsetRenderer(
            $this->_oldFieldsetRenderer
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->_oldFieldsetElementRenderer
        );
        */
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        //Mage::helper('udropship/catalog')->setDesignStore();
        return parent::_afterToHtml($html);
    }

    public function getPidBySku($sku)
    {
        return Mage::helper('udropship/catalog')->getPidBySku($sku);
    }

    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getProductVendor($product=null)
    {
        if (null === $product) {
            $product = $this->getProduct();
        }
        return Mage::helper('udropship')->getVendor($product->getUdropshipVendor());
    }

    public function isMyProduct($product=null)
    {
        $product = $product ? $product : $this->getProduct();
        return !$product->getId()
            || $this->getVendor()->getId() == $this->getProductVendor($product)->getId();
    }

    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = Mage::helper('udprod')->initProductEdit(array(
                'id' => Mage::app()->getRequest()->getParam('id'),
                'vendor' => $this->getVendor()
            ));
            Mage::register('current_product', $this->_product);
            Mage::register('product', $this->_product);
        }
        return $this->_product;
    }

    protected function _prepareLayout()
    {
        if (!$this->getData('skip_add_head_js')) {
            $this->getLayout()->getBlock('head')->addJs('unirgy/uploader.js');
            $this->getLayout()->getBlock('head')->addJs('unirgy/admin/product.js');
        }
        return parent::_prepareLayout();
    }

    public function isQty($product=null)
    {
        if (null === $product) {
            $product = $this->getProduct();
        }
        return Mage::helper('udprod')->isQty($product);
    }

    protected function _addConfigurableSettings($prod, &$values)
    {
        $cfgFieldset = $this->_form->addFieldset('configurable',
            array(
                'legend'=>Mage::helper('udropship')->__('Add Product Options'),
                'class'=>'fieldset-wide',
        ));
        $this->addAdditionalElementType(
            'cfg_quick_create',
            Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_quickCreate')
        );
        $this->_addElementTypes($cfgFieldset);

        $cfgAttributes = $prod->getTypeInstance(true)
            ->getSetAttributes($prod);

        if ($prod->getId()) {
            $values['_cfg_attribute']['simple_skus'] = implode("\n",
                Mage::helper('udropship/catalog')->getCfgSimpleSkus($prod->getId())
            );
            $values['_cfg_attribute']['attributes'] = @array_combine(
                $prod->getTypeInstance(true)->getUsedProductAttributeIds($prod),
                $prod->getTypeInstance(true)->getUsedProductAttributeIds($prod)
            );
        }

        $cfgQcEl = $cfgFieldset->addField('_cfg_quick_create', 'cfg_quick_create', array(
            'name'      => '_cfg_attribute[quick_create]',
            'label'     => Mage::helper('udropship')->__('Simples Management'),
            'value_filter' => new Varien_Filter_Sprintf('%s', 2),
            'product' => $prod,
            'used_product_attributes' => Mage::helper('udprod')->getTplConfigurableAttributes(
                    $this->getVendor(),
                    $prod
                )
        ));
        $cfgQcEl->setProduct($prod);
        $cfgFieldset->setProduct($prod);
        $cfgFieldset->setRenderer($this->getLayout()->createBlock('udprod/vendor_product_renderer_quickCreateFieldset'));
        $cfgFieldset->getRenderer()->setProduct($prod);
        $this->_prepareFieldsetColumns($cfgFieldset);
    }

    protected function _addGroupedAssocProducts($prod, &$values)
    {
        $coFieldset = $this->_form->addFieldset('grouped_assoc_products',
            array(
                'legend'=>Mage::helper('udropship')->__('Associated Products'),
                'class'=>'fieldset-wide',
            ));
        $this->addAdditionalElementType(
            'grouped_assoc_products',
            Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_groupedAssocProducts')
        );
        $this->_addElementTypes($coFieldset);

        $coEl = $coFieldset->addField('_grouped_assoc_products', 'grouped_assoc_products', array(
            'name'      => 'options',
            'label'     => Mage::helper('udropship')->__('Associated Products'),
            'value_filter' => new Varien_Filter_Sprintf('%s', 2),
            'product' => $prod,
            'is_top'=>true,
        ));
        $coEl->setProduct($prod);
        $coFieldset->setProduct($prod);
        $coFieldset->setRenderer($this->getLayout()->createBlock('udprod/vendor_product_renderer_groupedAssocProductsFieldset'));
        $coFieldset->getRenderer()->setProduct($prod);
        $this->_prepareFieldsetColumns($coFieldset);
    }

    protected function _addCustomOptions($prod, &$values)
    {
        $coFieldset = $this->_form->addFieldset('custom_options',
            array(
                'legend'=>Mage::helper('udropship')->__('Custom Options'),
                'class'=>'fieldset-wide',
            ));
        $this->addAdditionalElementType(
            'custom_options',
            Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_customOptions')
        );
        $this->_addElementTypes($coFieldset);

        $coEl = $coFieldset->addField('_custom_options', 'custom_options', array(
            'name'      => 'options',
            'label'     => Mage::helper('udropship')->__('Custom Options Management'),
            'value_filter' => new Varien_Filter_Sprintf('%s', 2),
            'product' => $prod,
            'is_top'=>true,
        ));
        $coEl->setProduct($prod);
        $coFieldset->setProduct($prod);
        $coFieldset->setRenderer($this->getLayout()->createBlock('udprod/vendor_product_renderer_customOptionsFieldset'));
        $coFieldset->getRenderer()->setProduct($prod);
        $this->_prepareFieldsetColumns($coFieldset);
    }

    protected function _addDownloadableOptions($prod, &$values)
    {
        $coFieldset = $this->_form->addFieldset('downloadable_options',
            array(
                'legend'=>Mage::helper('udropship')->__('Downloadable Options'),
                'class'=>'fieldset-wide',
            ));
        $this->addAdditionalElementType(
            'downloadable_options',
            Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_downloadable')
        );
        $this->_addElementTypes($coFieldset);

        $coEl = $coFieldset->addField('_downloadable_options', 'downloadable_options', array(
            'name'      => 'options',
            'label'     => Mage::helper('udropship')->__('Downloadable Options Management'),
            'value_filter' => new Varien_Filter_Sprintf('%s', 2),
            'product' => $prod,
            'is_top'=>true,
        ));
        $coEl->setProduct($prod);
        $coFieldset->setProduct($prod);
        $coFieldset->setRenderer($this->getLayout()->createBlock('udprod/vendor_product_renderer_downloadableFieldset'));
        $coFieldset->getRenderer()->setProduct($prod);
        $this->_prepareFieldsetColumns($coFieldset);
    }

    protected function _getWebsiteValues()
    {
        return Mage::helper('udropship/catalog')->getWebsiteValues();
    }

    protected function _getCategoryValues()
    {
        return Mage::helper('udropship/catalog')->getCategoryValues();
    }

    protected function _getStockItemField($field, $values)
    {
        return Mage::helper('udprod/form')->getStockItemField($field, $values);
    }
    protected function _getAttributeField($attribute)
    {
        return Mage::helper('udprod/form')->getAttributeField($attribute);
    }
    protected function _getUdmultiField($field, $mvData)
    {
        return Mage::helper('udprod/form')->getUdmultiField($field, $mvData);
    }
    protected function _getSystemField($field, $values)
    {
        return Mage::helper('udprod/form')->getSystemField($field, $values);
    }

    public function getForm()
    {
        if (null === $this->_form) {
            $prod = $this->getProduct();
            
            $hideFields = Mage::helper('udprod')->getHideEditFields();

            $this->_form = new Varien_Data_Form();
            $this->_form->setDataObject($prod);

            $values = $prod->getData();

            if ($prod->getStockItem()) {
                $values = array_merge($values, array('stock_data'=>$prod->getStockItem()->getData()));
            }
            if (($udFormData = Mage::getSingleton('udropship/session')->getUdprodFormData(true))
                && is_array($udFormData)
            ) {
                unset($udFormData['media_gallery']);
                $values = array_merge($values, $udFormData);
            }

            $mvData = array();
            $v = $this->getVendor();
            if (!empty($values['udmulti'])) {
                $mvData = $values['udmulti'];
            } else {
                if (Mage::helper('udropship')->isUdmultiActive()) {
                    Mage::helper('udmulti')->attachMultivendorData(array($prod), false, true);
                    $mvData = $prod->getAllMultiVendorData($v->getId());
                    $mvData = !empty($mvData) ? $mvData : array();
                }
            }

            $cId = $prod->getCategoryIds();
            if (empty($cId) && !Mage::helper('udprod')->getUseTplProdCategoryBySetId($prod)) {
                $cId = Mage::helper('udprod')->getDefaultCategoryBySetId($prod);
            }
            $values['product_categories'] = @implode(',', (array)$cId);

            $wId = $prod->getWebsiteIds();
            if (empty($wId) && !Mage::helper('udprod')->getUseTplProdWebsiteBySetId($prod)) {
                $wId = Mage::helper('udprod')->getDefaultWebsiteBySetId($prod);
            }
            $values['product_websites'] = @implode(',', (array)$wId);

            $fsIdx = 0;
            $skipInputType = array('media_image');
            if ('configurable' == $prod->getTypeId()) {
                $skipInputType[] = 'gallery';
            }
            $fieldsetsConfig = Mage::getStoreConfig('udprod/form/fieldsets');
            if (!is_array($fieldsetsConfig)) {
                $fieldsetsConfig = Mage::helper('udropship')->unserialize($fieldsetsConfig);
            }
            $_attributes = $prod->getAttributes();
            $attributes = array();
            foreach ($_attributes as $_attr) {
                $attributes[$_attr->getAttributeCode()] = $_attr;
            }
            $includedFields = array();
            $emptyForm = true;
            if (is_array($fieldsetsConfig)) {
            foreach ($fieldsetsConfig as $fsConfig) {
            if (is_array($fsConfig)) {
                $fields = array();

                foreach (array('top_columns','bottom_columns','left_columns','right_columns') as $colKey) {
                if (isset($fsConfig[$colKey]) && is_array($fsConfig[$colKey])) {
                    $requiredFields = (array)@$fsConfig['required_fields'];
                    foreach ($fsConfig[$colKey] as $fieldCode) {
                        if (!$this->_isFieldApplicable($prod, $fieldCode, $fsConfig)) continue;
                        $field = array();
                        if (strpos($fieldCode, 'product.') === 0
                            && !in_array(substr($fieldCode, 8), $hideFields)
                            && isset($attributes[substr($fieldCode, 8)])
                            && $this->isMyProduct()
                        ) {
                            if (($field = $this->_getAttributeField($attributes[substr($fieldCode, 8)]))) {
                                $field['product_attribute'] = $attributes[substr($fieldCode, 8)];
                            }
                        } elseif (strpos($fieldCode, 'udmulti.') === 0) {
                            $field = $this->_getUdmultiField(substr($fieldCode, 8), $mvData);
                        } elseif (strpos($fieldCode, 'stock_data.') === 0) {
                            $field = $this->_getStockItemField(substr($fieldCode, 11), $values);
                        } elseif (strpos($fieldCode, 'system.') === 0) {
                            $field = $this->_getSystemField(substr($fieldCode, 7), $values);
                        }
                        if (!empty($field) && !in_array($field['type'], $skipInputType)) {
                            switch ($colKey) {
                                case 'top_columns':
                                    $field['is_top'] = true;
                                    break;
                                case 'bottom_columns':
                                    $field['is_bottom'] = true;
                                    break;
                                case 'right_columns':
                                    $field['is_right'] = true;
                                    break;
                                default:
                                    $field['is_left'] = true;
                                    break;
                            }
                            if (in_array($fieldCode, $requiredFields)) {
                                $field['required'] = true;
                            } else {
                                $field['required'] = false;
                                if (!empty($field['class'])) {
                                    $field['class'] = str_replace('required-entry', '', $field['class']);
                                }
                            }
                            if (in_array($field['name'], $includedFields)) continue;
                            $includedFields[] = $field['name'];
                            $fields[] = $field;
                        }
                    }
                }}

                if (!empty($fields)) {
                    if ($fsIdx==0) {
                        /*
                        $fields = array_merge(array(
                        'product_categories' => array(
                            'id'=>'product_categories',
                            'is_top'=>true,
                            'is_hidden'=>true,
                            'type'=>'hidden',
                            'name'=>'category_ids',
                            'value'=>$values['product_categories'],
                        ),
                        'product_websites' => array(
                            'id'=>'product_websites',
                            'is_top'=>true,
                            'is_hidden'=>true,
                            'type'=>'hidden',
                            'name'=>'website_ids',
                            'value'=>@implode(',', (array)$wId),
                        ),
                        ), $fields);
                        */
                    }
                    $fsIdx++;
                    $fieldset = $this->_form->addFieldset('group_fields'.$fsIdx,
                        array(
                            'legend'=>$fsConfig['title'],
                            'class'=>'fieldset-wide',
                    ));
                    $this->_addElementTypes($fieldset);
                    foreach ($fields as $field) {
                        if (!empty($field['input_renderer'])) {
                            $fieldset->addType($field['type'], $field['input_renderer']);
                        }
                        $formField = $fieldset->addField($field['id'], $field['type'], $field);
                        if (!empty($field['renderer'])) {
                            $formField->setRenderer($field['renderer']);
                        }
                    }
                    $this->_prepareFieldsetColumns($fieldset);
                    $emptyForm = false;
                }
            }}}

            if (!$prod->getId()) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if (!$emptyForm) {
                if ('configurable' == $prod->getTypeId()) {
                    $this->_addConfigurableSettings($prod, $values);
                }
                if ('configurable' != $prod->getTypeId()
                    || Mage::getStoreConfigFlag('udprod/general/cfg_show_media_gallery')
                ) {
                    $cfgHideEditFields = explode(',', Mage::getStoreConfig('udropship/microsite/hide_product_attributes'));
                    if (isset($attributes['media_gallery'])
                        && !in_array('media_gallery', $cfgHideEditFields)
                        && $this->isMyProduct()
                    ) {
                        $attribute = $attributes['media_gallery'];
                        if ($attribute && (!$attribute->hasIsVisible() || $attribute->getIsVisible())
                            && ($inputType = $attribute->getFrontend()->getInputType())
                        ) {
                            $fieldset = $this->_form->addFieldset('group_fields_images',
                                array(
                                    'legend'=>Mage::helper('udropship')->__('Images'),
                                    'class'=>'fieldset-wide',
                            ));
                            $this->_addElementTypes($fieldset);
                            $fieldType      = $inputType;
                            $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                            if (!empty($rendererClass)) {
                                $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                                $fieldset->addType($fieldType, $rendererClass);
                            }

                            $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                                array(
                                    'name'      => $attribute->getAttributeCode(),
                                    'label'     => $attribute->getFrontend()->getLabel(),
                                    'class'     => $attribute->getFrontend()->getClass(),
                                    'required'  => $attribute->getIsRequired(),
                                    'note'      => $attribute->getNote(),
                                    'is_top'    => true
                                )
                            )
                            ->setExplicitSection(true)
                            ->setEntityAttribute($attribute);
                            $this->_prepareFieldsetColumns($fieldset);
                        }
                    }
                }
                if (Mage::getStoreConfigFlag('udprod/general/allow_custom_options')) {
                    $this->_addCustomOptions($prod, $values);
                }
                if ('downloadable' == $prod->getTypeId()) {
                    $this->_addDownloadableOptions($prod, $values);
                }
                if ('grouped' == $prod->getTypeId()) {
                    $this->_addGroupedAssocProducts($prod, $values);
                }
            }

            $this->_form->addValues($values);

            $this->_form->setFieldNameSuffix('product');
        }
        return $this->_form;
    }

    protected function _isFieldApplicable($prod, $fieldCode, $fsConfig)
    {
        $result = true;
        $ult = @$fsConfig['fields_extra'][$fieldCode]['use_limit_type'];
        $lt = @$fsConfig['fields_extra'][$fieldCode]['limit_type'];
        if (!is_array($lt)) {
            $lt = explode(',', $lt);
        }
        if ($ult && !in_array($prod->getTypeId(), $lt)) {
            $result = false;
        }
        if (strpos($fieldCode, 'udmulti.') === 0
            && !Mage::helper('udropship')->isUdmultiActive()
        ) {
            $result = false;
        }
        if (strpos($fieldCode, 'stock_data.') === 0
            && Mage::helper('udropship')->isUdmultiActive()
        ) {
            $result = false;
        }
        return $result;
    }

    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $bottomElements = $topElements = $lcElements = $rcElements = array();
        while($element=current($elements)) {
            if ($element->getIsBottom()) {
                $bottomElements[] = $element->getId();
            } elseif ($element->getIsTop()) {
                $topElements[] = $element->getId();
            } elseif ($element->getIsRight()) {
                $rcElements[] = $element->getId();
            } else {
                $lcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setTopColumn($topElements);
        $fieldset->setBottomColumn($bottomElements);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }

    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = array(
                'stock_data_qty'=> Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_stockDataQty'),
                'tier_price'=> Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_tierPrice'),
                'group_price'=> Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_groupPrice'),
                'price'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
                'weight'   => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_weight'),
                'gallery'  => Mage::getConfig()->getBlockClassName('udprod/vendor_product_gallery'),
                'image'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_image'),
                'boolean'  => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
                'textarea' => Mage::getConfig()->getBlockClassName('udprod/vendor_product_wysiwyg'),
                'product_categories' => Mage::getConfig()->getBlockClassName('udropship/categoriesField'),
            );

            $events = array('adminhtml_catalog_product_edit_element_types', 'udprod_product_edit_element_types');
            foreach ($events as $event) {
                $response = new Varien_Object();
                $response->setTypes(array());
                Mage::dispatchEvent($event, array('response'=>$response));
                foreach ($response->getTypes() as $typeName=>$typeClass) {
                    $result[$typeName] = $typeClass;
                }
            }

            $this->_additionalElementTypes = $result;
        }
        return $this;
    }

    protected function _getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }

    protected function _addElementTypes(Varien_Data_Form_Abstract $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

}