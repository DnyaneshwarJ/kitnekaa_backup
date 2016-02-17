<?php

class Unirgy_DropshipVendorProduct_Model_ProductTypeConfigurableOSP extends Unirgy_DropshipVendorProduct_Model_ProductTypeConfigurableOSP2
{
    public function getConfigurableAttributesAsArray($product = null)
    {
        $res = array();
        foreach ($this->getConfigurableAttributes($product) as $attribute) {
            $res[] = array(
                'id'             => $attribute->getId(),
                'label'          => $attribute->getLabel(),
                'use_default'    => $attribute->getUseDefault(),
                'identify_image' => $attribute->getIdentifyImage(),
                'position'       => $attribute->getPosition(),
                'values'         => $attribute->getPrices() ? $attribute->getPrices() : array(),
                'attribute_id'   => $attribute->getProductAttribute()->getId(),
                'attribute_code' => $attribute->getProductAttribute()->getAttributeCode(),
                'frontend_label' => $attribute->getProductAttribute()->getFrontend()->getLabel(),
                'store_label'    => $attribute->getProductAttribute()->getStoreLabel(),
            );
        }
        return $res;
    }

    public function getAttributeById($attributeId, $product = null)
    {
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if (1||$this->_inUnirgyPreload) {
            $result = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeId);
        } else {
            $result = parent::getAttributeById($attributeId, $product);
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $result;
    }

    protected $_inUnirgyPreload = false;
    public function unirgyPreloadProducts($products, $allAttributes=false)
    {
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        $this->_inUnirgyPreload = true;
        $_products = array();
        foreach ($products as $p) {
            if ($p->getTypeId() == 'configurable' && !$p->hasData($this->_configurableAttributes)) {
                $_products[$p->getId()] = $p;
            }
        }
        $configurableAttributes = array();
        $requiredAttributeIdsByPid = array();
        foreach ($_products as $p) {
            $p->setIsProductListFlag($allAttributes ? 2 : 1);
            $cfgAttrs = $this->getConfigurableAttributeCollection($p)->orderByPosition();
            $cfgAttrs->setFlag('unirgy_skip_afterload',1)->load();
            $requiredAttributeIdsByPid[$p->getId()] = $cfgAttrs->getColumnValues('attribute_id');
            $configurableAttributes[$p->getId()] = $cfgAttrs;
        }

        $_res = Mage::getSingleton('core/resource');
        $_conn = $_res->getConnection('core_read');

        $usedPidUnions = array();
        foreach ($_products as $p) {
            $attrSel = clone $p->getPTCAColSel();
            $attrSel
                ->reset(Zend_Db_Select::COLUMNS)
                ->reset(Zend_Db_Select::HAVING)
                ->reset(Zend_Db_Select::LIMIT_COUNT)
                ->reset(Zend_Db_Select::GROUP)
                ->reset(Zend_Db_Select::LIMIT_OFFSET)
                ->group('av.value')
                ->where('main_table.attribute_id in (?)', $requiredAttributeIdsByPid[$p->getId()])
                ->columns(array('lsl.product_id', 'parent_id' => new Zend_Db_Expr($p->getId())));
            $usedPidUnions[] = $attrSel;
        }
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__.'$usedPidUnionSel');
        if (!empty($usedPidUnions)) {
            $usedPidUnionSel = $_conn->select()->union($usedPidUnions);
	        $usedPids = $_conn->fetchAll($usedPidUnionSel);
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__.'$usedPidUnionSel');

        $requiredAttributeIds = array();
        if (!empty($usedPids)) {
            $usedCol = Mage::getModel('catalog/product')->getCollection();
            $usedCol->addAttributeToFilter('entity_id', array('in'=>array_keys($usedPids)));
            foreach ($requiredAttributeIdsByPid as $pId => $rAttrs) {
                foreach ($rAttrs as $attributeId) {
                    Varien_Profiler::start('CONFIGURABLE:'.__METHOD__.'getAttributeById');
                    $attribute = $this->getAttributeById($attributeId, $_products[$pId]);
                    Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__.'getAttributeById');
                    if (!is_null($attribute) && empty($requiredAttributeIds[$attributeId])) {
                        $usedCol->addAttributeToSelect($attribute->getAttributeCode());
                        $requiredAttributeIds[$attributeId] = 1;
                    }
                }
            }
            Varien_Profiler::start('CONFIGURABLE:'.__METHOD__.'usedCol->load()');
            $usedCol->load();
            Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__.'usedCol->load()');
            $usedProducts = array();
            foreach ($usedCol as $up) {
                foreach ($usedPids as $uPid) {
                    if ($uPid['product_id'] == $up->getId()) {
                        $usedProducts[$uPid['parent_id']][] = $up;
                    }
                }
            }
            foreach ($usedProducts as $pId => $ups) {
                $_products[$pId]->setData($this->_usedProducts, $ups);
            }
        }

        foreach ($configurableAttributes as $pId => $cfgAttrs) {
            $cfgAttrs->setFlag('unirgy_skip_afterload',0)->myAfterLoad();
            $_products[$pId]->setData($this->_configurableAttributes, $cfgAttrs);
        }
        $this->_inUnirgyPreload = false;
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $this;
    }
}
