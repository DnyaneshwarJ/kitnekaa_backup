<?php

class Unirgy_DropshipVendorPromotions_Block_Vendor_Rule extends Mage_Core_Block_Template
{
    protected $_form;
    protected $_rule;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('udpromo/vendor_rule_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('udpromo/vendor_rule_renderer_fieldsetElement')
        );

        return $this;
    }
    public function getForm()
    {
        if (null === $this->_form) {
            $rule = $this->getRule();
            $this->_form = new Varien_Data_Form();
            $this->_form->setDataObject($rule);
            $values = $rule->getData();

            if (($udFormData = Mage::getSingleton('udropship/session')->getUdpromoData(true))
                && is_array($udFormData)
            ) {
                $values = array_merge($values, $udFormData);
            }

            $this->_addGeneralFieldset($rule, $values);
            $this->_addConditionsFieldset($rule, $values);
            $this->_addActionsFieldset($rule, $values);
            $this->_addActionsFilterFieldset($rule, $values);
            //$this->_addCouponsFieldset($rule, $values);

            $this->_form->addValues($values);

            //$this->_form->setFieldNameSuffix('rule');
        }
        return $this->_form;
    }
    public function getRule()
    {
        if (null === $this->_rule) {
            $this->_rule = Mage::getModel('salesrule/rule')->load(
                Mage::app()->getRequest()->getParam('id')
            );
            Mage::register('current_promo_quote_rule', $this->_rule);
        }
        return $this->_rule;
    }
    protected function _addGeneralFieldset($rule, &$values)
    {
        $fieldset = $this->_form->addFieldset('general_fieldset',
            array(
                'legend'=>Mage::helper('udropship')->__('General'),
                'class'=>'fieldset-wide',
        ));

        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);

        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
                'is_wide'=>true,
                'is_top'=>true,
                'is_hidden'=>true,
            ));
        }

        $fieldset->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
            'is_wide'=>true,
            'is_top'=>true,
            'is_hidden'=>true,
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('udropship')->__('Name'),
            'title' => Mage::helper('udropship')->__('Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('udropship')->__('Description'),
            'title' => Mage::helper('udropship')->__('Description'),
            'style' => 'height: 100px;',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('udropship')->__('Status'),
            'title'     => Mage::helper('udropship')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('udropship')->__('Active'),
                '0' => Mage::helper('udropship')->__('Inactive'),
            ),
        ));

        if (!$rule->getId()) {
            $rule->setData('is_active', '1');
            $values['is_active'] = 1;
        }

        $usesPerCouponFiled = $fieldset->addField('uses_per_coupon', 'text', array(
            'name' => 'uses_per_coupon',
            'label' => Mage::helper('udropship')->__('Uses per Coupon'),
        ));

        $fieldset->addField('uses_per_customer', 'text', array(
            'name' => 'uses_per_customer',
            'label' => Mage::helper('udropship')->__('Uses per Customer'),
            'note' => Mage::helper('udropship')->__('Usage limit enforced for logged in customers only'),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('udropship')->__('From Date'),
            'title'  => Mage::helper('udropship')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('udropship')->__('To Date'),
            'title'  => Mage::helper('udropship')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        /*
        $couponTypeFiled = $fieldset->addField('coupon_type', 'select', array(
            'name'       => 'coupon_type',
            'label'      => Mage::helper('udropship')->__('Coupon'),
            'required'   => true,
            'options'    => Mage::getModel('salesrule/rule')->getCouponTypes(),
            'is_bottom'=>true,
            'is_wide'=>true
        ));
        */

        $couponCodeFiled = $fieldset->addField('coupon_code', 'text', array(
            'name' => 'coupon_code',
            'label' => Mage::helper('udropship')->__('Coupon Code'),
            'required' => false,
            'is_bottom'=>true,
            'is_wide'=>true
        ));

        /*
        $autoGenerationCheckbox = $fieldset->addField('use_auto_generation', 'checkbox', array(
            'name'  => 'use_auto_generation',
            'label' => Mage::helper('udropship')->__('Use Auto Generation'),
            'note'  => Mage::helper('udropship')->__('If you select and save the rule you will be able to generate multiple coupon codes.'),
            'onclick' => 'handleCouponsTabContentActivity()',
            'checked' => (int)$rule->getUseAutoGeneration() > 0 ? 'checked' : '',
            'is_bottom'=>true,
            'is_wide'=>true
        ));

        $autoGenerationCheckbox->setRenderer(
            $this->getLayout()->createBlock('udpromo/vendor_rule_renderer_autoCheckbox')
        );

        $fieldset->setData('udpromo_form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($couponTypeFiled->getHtmlId(), $couponTypeFiled->getName())
                ->addFieldMap($couponCodeFiled->getHtmlId(), $couponCodeFiled->getName())
                ->addFieldMap($autoGenerationCheckbox->getHtmlId(), $autoGenerationCheckbox->getName())
                ->addFieldMap($usesPerCouponFiled->getHtmlId(), $usesPerCouponFiled->getName())
                ->addFieldDependence(
                    $couponCodeFiled->getName(),
                    $couponTypeFiled->getName(),
                    Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                ->addFieldDependence(
                    $autoGenerationCheckbox->getName(),
                    $couponTypeFiled->getName(),
                    Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                ->addFieldDependence(
                    $usesPerCouponFiled->getName(),
                    $couponTypeFiled->getName(),
                    Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
        );
        */

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }

    protected function _addConditionsFieldset($rule, &$values)
    {
        $renderer = Mage::getBlockSingleton('udpromo/vendor_rule_renderer_fieldset_conditions')
            ->setTemplate('unirgy/udpromo/vendor/rule/renderer/fieldset/conditions.phtml')
            ->setNewChildUrl($this->getUrl('udpromo/vendor/newConditionHtml/form/conditions_fieldset'));

        $fieldset = $this->_form->addFieldset('conditions_fieldset',
            array(
                'legend'=>Mage::helper('udropship')->__('Conditions [Apply the rule only if the following conditions are met (leave blank for all products)]'),
                'class'=>'fieldset-wide',
            ))->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);

        $fieldset->addField('conditions', 'text', array(
            'switch_adminhtml'=>true,
            'name' => 'rule[conditions]',
            'label' => Mage::helper('udropship')->__('Conditions'),
            'title' => Mage::helper('udropship')->__('Conditions'),
            'is_top'=>true,
            'is_wide'=>true,
        ))->setRule($rule)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addActionsFieldset($rule, &$values)
    {
        $fieldset = $this->_form->addFieldset('actions_fieldset',
            array(
                'legend'=>Mage::helper('udropship')->__('Actions'),
                'class'=>'fieldset-wide',
            ));
        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);

        $fieldset->addField('simple_action', 'select', array(
            'label'     => Mage::helper('udropship')->__('Apply'),
            'name'      => 'simple_action',
            'options'    => array(
                Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('udropship')->__('Percent of product price discount'),
                Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('udropship')->__('Fixed amount discount'),
                Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => Mage::helper('udropship')->__('Fixed amount discount for whole cart'),
                Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION => Mage::helper('udropship')->__('Buy X get Y free (discount amount is Y)'),
            ),
        ));
        $fieldset->addField('discount_amount', 'text', array(
            'name' => 'discount_amount',
            'required' => true,
            'class' => 'validate-not-negative-number',
            'label' => Mage::helper('udropship')->__('Discount Amount'),
        ));
        $rule->setDiscountAmount($rule->getDiscountAmount()*1);

        $fieldset->addField('discount_qty', 'text', array(
            'name' => 'discount_qty',
            'label' => Mage::helper('udropship')->__('Maximum Qty Discount is Applied To'),
        ));
        $rule->setDiscountQty($rule->getDiscountQty()*1);

        $fieldset->addField('discount_step', 'text', array(
            'name' => 'discount_step',
            'label' => Mage::helper('udropship')->__('Discount Qty Step (Buy X)'),
        ));

        $fieldset->addField('apply_to_shipping', 'select', array(
            'label'     => Mage::helper('udropship')->__('Apply to Shipping Amount'),
            'title'     => Mage::helper('udropship')->__('Apply to Shipping Amount'),
            'name'      => 'apply_to_shipping',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('simple_free_shipping', 'select', array(
            'label'     => Mage::helper('udropship')->__('Free Shipping'),
            'title'     => Mage::helper('udropship')->__('Free Shipping'),
            'name'      => 'simple_free_shipping',
            'options'    => array(
                0 => Mage::helper('udropship')->__('No'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM => Mage::helper('udropship')->__('For matching items only'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS => Mage::helper('udropship')->__('For shipment with matching items'),
            ),
        ));

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addActionsFilterFieldset($rule, &$values)
    {
        $renderer = Mage::getBlockSingleton('udpromo/vendor_rule_renderer_fieldset_actionsFilter')
            ->setTemplate('unirgy/udpromo/vendor/rule/renderer/fieldset/actions_filter.phtml')
            ->setNewChildUrl($this->getUrl('udpromo/vendor/newActionHtml/form/actions_filter_fieldset'));

        $fieldset = $this->_form->addFieldset('actions_filter_fieldset',
            array(
                'legend'=>Mage::helper('udropship')->__('Actions Filter [Apply the rule only to cart items matching the following conditions (leave blank for all items)]'),
                'class'=>'fieldset-wide',
            ))->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);

        $fieldset->addField('actions', 'text', array(
            'name' => 'rule[actions]',
            'label' => Mage::helper('udropship')->__('Apply To'),
            'title' => Mage::helper('udropship')->__('Apply To'),
            'required' => true,
            'is_wide'=>true,
            'is_top'=>true,
        ))->setRule($rule)->setRenderer(Mage::getBlockSingleton('rule/actions'));

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addCouponsFieldset($rule, &$values)
    {
        $renderer = Mage::getBlockSingleton('udpromo/vendor_rule_renderer_fieldset_coupons')
            ->setTemplate('unirgy/udpromo/vendor/rule/renderer/fieldset/coupons.phtml');

        $fieldset = $this->_form->addFieldset('coupons_fieldset',
            array(
                'legend'=>Mage::helper('udropship')->__('Coupons'),
                'class'=>'fieldset-wide',
            ))->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);
        $couponHelper = Mage::helper('salesrule/coupon');

        $model = Mage::registry('current_promo_quote_rule');
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('promo_quote_edit_tab_coupons_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', array('legend'=>Mage::helper('udropship')->__('Coupons Information')));
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', array(
            'name'     => 'rule_id',
            'value'    => $ruleId
        ));

        $fieldset->addField('qty', 'text', array(
            'name'     => 'qty',
            'label'    => Mage::helper('udropship')->__('Coupon Qty'),
            'title'    => Mage::helper('udropship')->__('Coupon Qty'),
            'required' => true,
            'class'    => 'validate-digits validate-greater-than-zero'
        ));

        $fieldset->addField('length', 'text', array(
            'name'     => 'length',
            'label'    => Mage::helper('udropship')->__('Code Length'),
            'title'    => Mage::helper('udropship')->__('Code Length'),
            'required' => true,
            'note'     => Mage::helper('udropship')->__('Excluding prefix, suffix and separators.'),
            'value'    => $couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero'
        ));

        $fieldset->addField('format', 'select', array(
            'label'    => Mage::helper('udropship')->__('Code Format'),
            'name'     => 'format',
            'options'  => $couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $couponHelper->getDefaultFormat()
        ));

        $fieldset->addField('prefix', 'text', array(
            'name'  => 'prefix',
            'label' => Mage::helper('udropship')->__('Code Prefix'),
            'title' => Mage::helper('udropship')->__('Code Prefix'),
            'value' => $couponHelper->getDefaultPrefix()
        ));

        $fieldset->addField('suffix', 'text', array(
            'name'  => 'suffix',
            'label' => Mage::helper('udropship')->__('Code Suffix'),
            'title' => Mage::helper('udropship')->__('Code Suffix'),
            'value' => $couponHelper->getDefaultSuffix()
        ));

        $fieldset->addField('dash', 'text', array(
            'name'  => 'dash',
            'label' => Mage::helper('udropship')->__('Dash Every X Characters'),
            'title' => Mage::helper('udropship')->__('Dash Every X Characters'),
            'note'  => Mage::helper('udropship')->__('If empty no separation.'),
            'value' => $couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits'
        ));

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField('generate_button', 'note', array(
            'text' => $this->getButtonHtml(
                    Mage::helper('udropship')->__('Generate'),
                    "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                    'generate'
                )
        ));

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }

    public function getGenerateUrl()
    {
        return $this->getUrl('udpromo/vendor/generate');
    }

    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $fullCnt = count($elements);
        $wideElementsBottom = $wideElements = $lcElements = $rcElements = array();
        while($element=current($elements)) {
            if ($element->getIsWide()) {
                if ($element->getIsBottom()) {
                    $wideElementsBottom[] = $element->getId();
                } else {
                    $wideElements[] = $element->getId();
                }
                $fullCnt--;
            }
            next($elements);
        }
        $halfCnt = ceil($fullCnt/2);
        reset($elements);
        $i=0; while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $lcElements[] = $element->getId();
                $i++;
            }
            next($elements);
            if ($i>=$halfCnt) break;
        }
        while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $rcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setWideColumnTop($wideElements);
        $fieldset->setWideColumnBottom($wideElementsBottom);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }
    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = array();

            $response = new Varien_Object();
            $response->setTypes(array());
            Mage::dispatchEvent('udpromo_rule_edit_element_types', array('response'=>$response));

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