<?php

class Unirgy_DropshipTierShipping_Block_Vendor_V2_Rates extends Mage_Core_Block_Template implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('unirgy/tiership/vendor/v2/rates.phtml');
        }
    }

    public function getTopCategories()
    {
        return Mage::helper('udtiership')->getTopCategories();
    }

    public function isShowAdditionalColumn()
    {
        return Mage::helper('udtiership')->isUseAdditional($this->getCalculationMethod());
    }

    public function isShowHandlingColumn()
    {
        return Mage::helper('udtiership')->isUseHandling($this->getHandlingApply());
    }

    public function isCtCostBasePlusZone()
    {
        return Mage::helper('udtiership')->isCtBasePlusZone($this->getCtCost());
    }
    public function isCtAdditionalBasePlusZone()
    {
        return Mage::helper('udtiership')->isCtBasePlusZone($this->getCtAdditional());
    }
    public function isCtHandlingBasePlusZone()
    {
        return Mage::helper('udtiership')->isCtBasePlusZone($this->getCtHandling());
    }

    public function getCtCost()
    {
        return $this->hasData('ct_cost')
            ? $this->getData('ct_cost')
            : Mage::getStoreConfig('carriers/udtiership/cost_calculation_type', $this->getStore());
    }
    public function getCtAdditional()
    {
        return $this->hasData('ct_additional')
            ? $this->getData('ct_additional')
            : Mage::getStoreConfig('carriers/udtiership/additional_calculation_type', $this->getStore());
    }
    public function getCtHandling()
    {
        return $this->hasData('ct_handling')
            ? $this->getData('ct_handling')
            : Mage::getStoreConfig('carriers/udtiership/handling_calculation_type', $this->getStore());
    }
    public function getHandlingApply()
    {
        return $this->hasData('handling_apply')
            ? $this->getData('handling_apply')
            : Mage::getStoreConfig('carriers/udtiership/handling_apply_method', $this->getStore());
    }
    public function getCalculationMethod()
    {
        return $this->hasData('calculation_method')
            ? $this->getData('calculation_method')
            : Mage::getStoreConfig('carriers/udtiership/calculation_method', $this->getStore());
    }

    public function getKeysForSubrows()
    {
        $res = array();
        if ($this->isCtCostBasePlusZone()) {
            $res[] = 'cost_extra';
        }
        if ($this->isShowAdditionalColumn() && $this->isCtAdditionalBasePlusZone()) {
            $res[] = 'additional_extra';
        }
        if ($this->isShowHandlingColumn() && $this->isCtHandlingBasePlusZone()) {
            $res[] = 'handling_extra';
        }
        return $res;
    }

    public function getStore()
    {
        return Mage::app()->getDefaultStoreView();
    }

    public function getSuffix($skType, $isBase)
    {
        $tsHlp = Mage::helper('udtiership');
        $suffix = '';
        $store = $this->getStore();
        switch ($skType) {
            case 'cost':
                if (!$isBase && $tsHlp->isUseCtPercentPerCustomerZone($this->getCtCost())) {
                    $suffix = '% to base';
                } elseif (!$isBase && $tsHlp->isUseCtFixedPerCustomerZone($this->getCtCost())) {
                    $suffix = 'fixed to base';
                } else {
                    $suffix = (string)$store->getBaseCurrencyCode();
                }
                break;
            case 'additional':
                if (!$isBase && $tsHlp->isUseCtPercentPerCustomerZone($this->getCtAdditional())) {
                    $suffix = '% to base';
                } elseif (!$isBase && $tsHlp->isUseCtFixedPerCustomerZone($this->getCtAdditional())) {
                    $suffix = 'fixed to base';
                } else {
                    $suffix = (string)$store->getBaseCurrencyCode();
                }
                break;
            case 'handling':
                if (!$tsHlp->isNoneValue($this->getHandlingApply())) {
                    if (!$isBase && $tsHlp->isUseCtPercentPerCustomerZone($this->getCtHandling())) {
                        $suffix = '% to base';
                    } elseif (!$isBase && $tsHlp->isUseCtFixedPerCustomerZone($this->getCtHandling())) {
                        $suffix = 'fixed to base';
                    } else {
                        if ($tsHlp->isPercentValue($this->getHandlingApply())) {
                            $suffix = '%';
                        } else {
                            $suffix = (string)$store->getBaseCurrencyCode();
                        }
                    }
                }
                break;
        }
        return $suffix;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        if (!$element->getDeliveryType()) {
            $html = '<div id="'.$element->getHtmlId().'_container"></div>';
        } else {
            $html = $this->toHtml();
        }
        return $html;
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }

    public function getSubrowsContainerBlock($fieldName, $skType)
    {
        return Mage::app()->getLayout()->getBlockSingleton('udtiership/vendor_v2_rates_subrows')
            ->setTemplate('unirgy/tiership/vendor/v2/rates/subrows.phtml')
            ->setFieldName($fieldName)
            ->setSuffix($this->getSuffix($skType, false))
            ->setParentBlock($this);
    }

}