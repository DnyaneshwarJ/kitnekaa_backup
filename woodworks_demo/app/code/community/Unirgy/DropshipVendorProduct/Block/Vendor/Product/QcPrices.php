<?php

class Unirgy_DropshipVendorProduct_Block_Vendor_Product_QcPrices extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('unirgy/udprod/vendor/product/qcprices.phtml');
        }
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

    public function getFieldName()
    {
        return $this->getData('field_name');
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

    public function getConfigurableAttributes()
    {
        return Mage::helper('udprod')->getConfigurableAttributes($this->getProduct(), !$this->getProduct()->getId());
    }

    public function getCfgAttrOptionsJson()
    {
        $cfgAttrs = $this->getConfigurableAttributes();
        $result = array();
        foreach ($cfgAttrs as $cfgAttr) {
            $result[$cfgAttr->getId()] = array(
                'value' => $cfgAttr->getId(),
                'label' => $cfgAttr->getFrontend()->getLabel(),
                'values' => $cfgAttr->getSource()->getAllOptions(true, true)
            );
        }
        return Zend_Json::encode($result);
    }

    public function getCfgPrices()
    {
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();
        $cfSel = $conn->select()->from(array('sa'=>$rHlp->getTable('catalog/product_super_attribute')), array('attribute_id'))
            ->join(array('sap'=>$rHlp->getTable('catalog/product_super_attribute_pricing')), 'sa.product_super_attribute_id=sap.product_super_attribute_id', array('value_index','is_percent','pricing_value'));
        $cfSel->where('sap.website_id=0');
        $cfSel->where('sa.product_id=?', (int)$this->getProduct()->getId());
        $cfSel->order('sa.product_super_attribute_id');
        $rows = $conn->fetchAll($cfSel);
        return $rows;
    }

}