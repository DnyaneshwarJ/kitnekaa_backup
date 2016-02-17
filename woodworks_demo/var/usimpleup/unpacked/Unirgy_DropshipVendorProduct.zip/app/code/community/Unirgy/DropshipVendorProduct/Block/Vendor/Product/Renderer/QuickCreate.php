<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_Renderer_QuickCreate extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        $this->setTemplate('unirgy/udprod/vendor/product/renderer/cfg_quick_create.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('udropship')->__('Delete'),
                    'class' => 'delete delete-option'
                )));
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('udropship')->__('Add'),
                    'class' => 'add',
                    'id'    => 'add_new_option_button'
                )));
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getStockStatusOptions()
    {
        return Mage::getSingleton('udprod/source')->setPath('stock_status')->toOptionHash(true);
    }
    public function getSystemStatusOptions()
    {
        return Mage::getSingleton('udprod/source')->setPath('system_status')->toOptionHash(true);
    }
    public function getUdmultiStatusOptions()
    {
        return Mage::getSingleton('udmulti/source')->setPath('vendor_product_status')->toOptionHash(true);
    }
    public function getUdmultiStateOptions()
    {
        return Mage::getSingleton('udmultiprice/source')->setPath('vendor_product_state')->toOptionHash(true);
    }

    public function getYesnoOptions()
    {
        return Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true);
    }

    public function getCfgAttributeLabels()
    {
        $cfgAttrs = $this->getFirstAttributes();
        $tuple = $this->getCfgAttributeValueTuple();
        $labels = array();
        foreach ($cfgAttrs as $__i => $__ca) {
            $labels[] = $__ca->getSource()->getOptionText($tuple[$__i]);
        }
        return $labels;
    }
    public function getCfgAttributeLabel()
    {
        return $this->getFirstAttribute()->getSource()->getOptionText($this->getCfgAttributeValue());
    }
    public function getCfgAttributeCode()
    {
        return $this->getCfgAttribute()->getAttributeCode();
    }
    public function getCfgAttribute()
    {
        return $this->getFirstAttribute();
    }
    public function getConfigurableAttributes($skipFirst=false)
    {
        $cfgAttrs = Mage::helper('udprod')->getConfigurableAttributes($this->getProduct(), !$this->getProduct()->getId());
        if ($skipFirst) {
            $firstAttr = $this->getFirstAttributes();
            $firstCnt = count($firstAttr);
            while (--$firstCnt>=0) array_shift($cfgAttrs);
        }
        return $cfgAttrs;
    }
    public function getFirstAttributes()
    {
        return Mage::helper('udprod')->getCfgFirstAttributes($this->getProduct());
    }
    public function getFirstAttribute()
    {
        return Mage::helper('udprod')->getCfgFirstAttribute($this->getProduct());
    }
    public function getFirstAttributesValues($used=null, $filters=array(), $filterFlag=true)
    {
        $values = array();
        $attrs = $this->getFirstAttributes();
        foreach ($attrs as $attr) {
            $values[] = $this->getAttributeValues($attr, $used, $filters, $filterFlag);
        }
        return $values;
    }
    public function getFirstAttributeValues($used=null, $filters=array(), $filterFlag=true)
    {
        return $this->getAttributeValues($this->getFirstAttribute(), $used, $filters, $filterFlag);
    }
    public function getAttributeValues($attribute, $used=null, $filters=array(), $filterFlag=true)
    {
        return Mage::helper('udprod')->getCfgAttributeValues($this->getProduct(), $attribute, $used, $filters, $filterFlag);
    }

    protected $_product;
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }
    public function getProduct()
    {
        return $this->_product;
    }

    public function getName()
    {
        $prod = $this->_element->getProduct();
        return $prod
            ? $this->_element->getProduct()->getName()
            : '';
    }

    public function getCfgData($key)
    {
        $prod = $this->_element->getProduct();
        return $prod
            ? $this->_element->getProduct()->getData($key)
            : '';
    }

    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getProductVendor()
    {
        return Mage::helper('udropship')->getVendor($this->getProduct()->getUdropshipVendor());
    }

    public function isMyProduct()
    {
        return !$this->getProduct()->getId()
            || $this->getVendor()->getId() == $this->getProductVendor()->getId();
    }

    public function getSimpleProducts($filtered=true)
    {
        $prod = $this->_element->getProduct();
        $cfgAttrs = $this->getFirstAttributes();
        $filter = array();
        $tuple = $this->getCfgAttributeValueTuple();
        foreach ($cfgAttrs as $__i => $__ca) {
            $filter[$__ca->getAttributeCode()] = $tuple[$__i];
        }
        return $prod ?
            ($filtered
                ? Mage::helper('udprod')->getFilteredSimpleProductData($prod, $filter)
                : Mage::helper('udprod')->getEditSimpleProductData($prod))
            : array();
    }

    protected $_galleryContent;
    public function getGalleryContent()
    {
        if (null === $this->_galleryContent) {
            $this->_galleryContent = $this->getLayout()->createBlock('udprod/vendor_product_galleryCfgContent');
            $this->_galleryContent->setCfgAttributes($this->getFirstAttributes());
            $this->_galleryContent->setCfgAttributeValueTuple($this->getCfgAttributeValueTuple());
            $this->_galleryContent->setForm($this->_element->getForm());
            $this->_galleryContent->setProduct($this->getProduct());
        }
        return $this->_galleryContent;
    }

    public function getGalleryContentHtml()
    {
        return $this->getGalleryContent()->toHtml();
    }

    public function isOneColumnCfgAttrs()
    {
        return 'one_column' == Mage::getStoreConfig('udprod/quick_create_layout/cfg_attributes');
    }

    public function getCfgAttrsColumnTitle()
    {
        return Mage::getStoreConfig('udprod/quick_create_layout/cfg_attributes_title');
    }

    protected $_columnsForm;
    public function getColumnsForm()
    {
        if (null !== $this->_columnsForm) {
            return $this->_columnsForm;
        }
        $htmlId = $this->_element->getId();
        $prod = $this->getProduct();
        $hideFields = Mage::helper('udprod')->getHideEditFields();
        $skipInputType = array('media_image');
        if ('configurable' == $prod->getTypeId()) {
            $skipInputType[] = 'gallery';
        }
        $attributes = Mage::helper('udprod')->getQuickCreateAttributes();
        $fsIdx = 0;
        $this->_columnsForm = new Varien_Data_Form();
        $columnsConfig = Mage::getStoreConfig('udprod/quick_create_layout/columns');
        if (!is_array($columnsConfig)) {
            $columnsConfig = Mage::helper('udropship')->unserialize($columnsConfig);
            if (is_array($columnsConfig)) {
            foreach ($columnsConfig as $fsConfig) {
            if (is_array($fsConfig)) {
                $fields = array();
                foreach (array('columns') as $colKey) {
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
                            $field = $this->_getAttributeField($attributes[substr($fieldCode, 8)]);
                        } elseif (strpos($fieldCode, 'udmulti.') === 0) {
                            $field = $this->_getUdmultiField(substr($fieldCode, 8), array());
                        } elseif (strpos($fieldCode, 'stock_data.') === 0) {
                            $field = $this->_getStockItemField(substr($fieldCode, 11), array());
                        }
                        if (!empty($field) && !in_array($field['type'], $skipInputType)) {
                            if (in_array($fieldCode, $requiredFields)) {
                                $field['required'] = true;
                            } else {
                                $field['required'] = false;
                                if (!empty($field['class'])) {
                                    $field['class'] = str_replace('required-entry', '', $field['class']);
                                }
                            }
                            $field['value'] = $this->prepareIdSuffix('$'.strtoupper($field['name']));
                            $field['id'] = $this->prepareIdSuffix($this->_columnsForm->addSuffixToName(
                                $field['name'],
                                $this->_element->getName().'[$ROW]'
                            ));
                            $fields[] = $field;
                        }
                    }
                }}

                if (!empty($fields)) {
                    $fsIdx++;
                    $fieldset = $this->_columnsForm->addFieldset('group_fields'.$fsIdx,
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
        }
        $this->_columnsForm->setDataObject($prod);
        $this->_columnsForm->addFieldNameSuffix($this->_element->getName().'[$ROW]');
        return $this->_columnsForm;
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

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    protected function _isFieldApplicable($prod, $fieldCode, $fsConfig)
    {
        $result = true;
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

    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = array(
                'stock_data_qty'=> Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_stockDataQty'),
                'price'    => Mage::getConfig()->getBlockClassName('udprod/vendor_product_form_price'),
                'weight'   => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_weight'),
                'gallery'  => Mage::getConfig()->getBlockClassName('udprod/vendor_product_gallery'),
                'image'    => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_image'),
                'boolean'  => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean'),
                'textarea' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_helper_form_wysiwyg')
            );

            $response = new Varien_Object();
            $response->setTypes(array());
            Mage::dispatchEvent('adminhtml_catalog_product_edit_element_types', array('response'=>$response));

            foreach ($response->getTypes() as $typeName=>$typeClass) {
                $result[$typeName] = $typeClass;
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