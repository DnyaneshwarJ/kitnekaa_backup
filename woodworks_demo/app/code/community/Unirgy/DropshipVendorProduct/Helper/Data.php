<?php

class Unirgy_DropshipVendorProduct_Helper_Data extends Mage_Core_Helper_Abstract
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

    public function getUdprodTemplateSku($vendor)
    {
        $value = $vendor->getUdprodTemplateSku();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function getGlobalTemplateSkuConfig()
    {
        $value = Mage::getStoreConfig('udprod/template_sku/value');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }

    public function getVendorTypeOfProductConfig($vendor=null)
    {
        if ($vendor==null) {
            $vendor = Mage::getSingleton('udropship/session')->getVendor();
        }
        return $this->_getTypeOfProductConfig($vendor);
    }

    public function getTypeOfProductConfig()
    {
        return $this->_getTypeOfProductConfig(false);
    }
    protected function _getTypeOfProductConfig($vendor)
    {
        $value = Mage::getStoreConfig('udprod/general/type_of_product');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        $setIds = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionHash();
        $config = array();
        if (is_array($value)) {
            foreach ($value as $val) {
                if ($vendor && !$this->isAllowedTypeOfProduct($val['type_of_product'], $vendor)) continue;
                $_setIds = $val['attribute_set'];
                if (is_array($_setIds) && !empty($_setIds)) {
                    $cfg = array(
                        'value'=>$val['type_of_product'],
                        'label'=>$val['type_of_product'],
                        'set_ids'=>array()
                    );
                    foreach ($_setIds as $_setId) {
                        if (!empty($setIds[$_setId])) {
                            $__setId = $_setId.'-'.$val['type_of_product'];
                            $cfg['set_ids']['__'.$__setId] = array(
                                'value'=>$__setId,
                                'label'=>$setIds[$_setId],
                                'is_configurable'=>$this->hasTplConfigurableAttributes(null,$__setId),
                                'is_downloadable'=>$this->isAllowedDownloadable(null,$__setId),
                                'is_grouped'=>$this->isAllowedGrouped(null,$__setId),
                                'is_virtual'=>$this->isAllowedVirtual(null,$__setId),
                                'is_simple'=>$this->isAllowedSimple(null,$__setId)
                            );
                        }
                    }
                    if (!empty($cfg['set_ids'])) {
                        $config[$val['type_of_product']] = $cfg;
                    }
                }
            }
        }
        return $config;
    }

    public function getTplProdBySetId($vendor, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        if (empty($setId)) {
            Mage::throwException('Type Of Product not specified');
        }
        list($_setId) = explode('-', $setId);
        $prTpl = Mage::getModel('udprod/product');
        $vTplSku = $this->getUdprodTemplateSku($vendor);
        if (isset($vTplSku[$setId]) && isset($vTplSku[$setId]['value'])
            && ($pId=Mage::helper('udropship/catalog')->getPidBySku($vTplSku[$setId]['value']))
        ) {
            $prTpl->load($pId);
        }
        if (!$prTpl->getId() && isset($vTplSku[$_setId]) && isset($vTplSku[$_setId]['value'])
            && ($pId=Mage::helper('udropship/catalog')->getPidBySku($vTplSku[$_setId]['value']))
        ) {
            $prTpl->load($pId);
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        if (!$prTpl->getId() && isset($gTplSku[$setId]) && isset($gTplSku[$setId]['value'])
            && ($pId=Mage::helper('udropship/catalog')->getPidBySku($gTplSku[$setId]['value']))
        ) {
            $prTpl->load($pId);
        }

        if (!$prTpl->getId() && isset($gTplSku[$_setId]) && isset($gTplSku[$_setId]['value'])
            && ($pId=Mage::helper('udropship/catalog')->getPidBySku($gTplSku[$_setId]['value']))
        ) {
            $prTpl->load($pId);
        } else {
            $prTpl->setAttributeSetId($_setId);
        }
        return $prTpl;
    }

    public function prepareTplProd($prTpl)
    {
        $prTpl->getWebsiteIds();
        $prTpl->getCategoryIds();
        $prTpl->setId(null);
        $prTpl->unsetData('entity_id');
        $prTpl->unsetData('sku');
        $prTpl->unsetData('url_key');
        $prTpl->unsetData('created_at');
        $prTpl->unsetData('updated_at');
        $prTpl->unsetData('has_options');
        $prTpl->unsetData('required_options');
        $prTpl->setStockItem(null);
        $prTpl->unsMediaGalleryImages();
        $prTpl->unsMediaGallery();
        $prTpl->resetTypeInstance();
        foreach (array(
            '_cache_instance_products',
            '_cache_instance_product_ids',
            '_cache_instance_configurable_attributes',
            '_cache_instance_used_attributes',
            '_cache_instance_used_product_attributes',
            '_cache_instance_used_product_attribute_ids',
        ) as $cfgKey) {
            $prTpl->unsetData($cfgKey);
        }
        return $this;
    }

    public function initProductEdit($config)
    {
        $r = Mage::app()->getRequest();
        $udSess = Mage::getSingleton('udropship/session');

        $pId         = array_key_exists('id', $config) ? $config['id'] : $r->getParam('id');
        $prTpl       = !empty($config['template_id']) ? $config['template_id'] : null;
        $typeId      = array_key_exists('type_id', $config) ? $config['type_id'] : $r->getParam('type_id');
        $setId       = array_key_exists('set_id', $config) ? $config['set_id'] : $r->getParam('set_id');
        $skipCheck   = !empty($config['skip_check']);
        $skipPrepare = !empty($config['skip_prepare']);
        $vendor      = !empty($config['vendor']) ? $config['vendor'] : $udSess->getVendor();
        $productData = !empty($config['data']) ? $config['data'] : array();

        list($_setId) = explode('-', $setId);

        $vendor = Mage::helper('udropship')->getVendor($vendor);

        if (!$vendor->getId()) {
            Mage::throwException('Vendor not specified');
        }

        $product = Mage::getModel('udprod/product')->setStoreId(0);
        if ($pId) {
            if (!$skipCheck) $this->checkProduct($pId);
            $product->load($pId);
        }
        if (!$product->getId()) {
            if (null === $prTpl) {
                $prTpl = $this->getTplProdBySetId($vendor, $setId);
            } else {
                $prTpl = Mage::getModel('udprod/product')->load($prTpl);
            }
            if ($setId) {
                $prTpl->setUdprodAttributeSetKey($setId);
                $prTpl->setAttributeSetId($_setId);
            }
            if (!$prTpl->getStockItem()) {
                $prTpl->setStockItem(Mage::getModel('cataloginventory/stock_item'));
            }
            $tplStockData = $prTpl->getStockItem()->getData();
            unset($tplStockData['item_id']);
            unset($tplStockData['product_id']);
            if (empty($productData['stock_data'])) {
                $productData['stock_data'] = array();
            }
            $productData['is_in_stock'] = !isset($productData['is_in_stock']) ? 1 : $productData['is_in_stock'];
            $productData['stock_data'] = array_merge($tplStockData, $productData['stock_data']);
            if (!isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 1;
            }
            if (isset($productData['stock_data']['qty']) && (float)$productData['stock_data']['qty'] > self::MAX_QTY_VALUE) {
                $productData['stock_data']['qty'] = self::MAX_QTY_VALUE;
            }
            $this->prepareTplProd($prTpl);
            $product->setData($prTpl->getData());
            $product->setData('__tpl_product', $prTpl);
            if (!$product->getAttributeSetId()) {
                $product->setAttributeSetId(
                    $product->getResource()->getEntityType()->getDefaultAttributeSetId()
                );
            }
            if ($typeId) {
                $product->setTypeId($typeId);
            } elseif (!$product->getTypeId()) {
                $product->setTypeId('simple');
            }
            if (!$product->hasData('status')) {
                $product->setData('status', Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING);
            }
            if (!$product->hasData('visibility')) {
                $product->setData('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            }
        }
        $product->setData('_edit_in_vendor', true);
        $product->setData('_edit_mode', true);
        if (is_array($productData)) {
            if (!$skipPrepare) $this->prepareProductPostData($product, $productData);
            $udmulti = @$productData['udmulti'];
            if (!isset($productData['price']) && is_array($udmulti) && isset($udmulti['vendor_price'])) {
                $productData['price'] = $udmulti['vendor_price'];
            }
            $product->addData($productData);
        }
        if (!$product->getId()) {
            $product->setUdropshipVendor($vendor->getId());
        }
        return $product;
    }

    public function prepareProductPostData($product, &$productData)
    {
        Mage::helper('udprod/protected')->prepareProductPostData($product, $productData);
        return $this;
    }

    public function processAfterSave($product)
    {
        $hideFields = explode(',', Mage::getStoreConfig('udropship/microsite/hide_product_attributes'));
        $hideFields[] = 'udropship_vendor';
        //$hideFields[] = 'tier_price';
        $hideFields[] = 'gallery';
        $hideFields[] = 'recurring_profile';
        $hideFields[] = 'media_gallery';
        $hideFields[] = 'updated_at';

        $attrChanged = $product->getData('udprod_attributes_changed');
        if (!is_array($attrChanged)) {
            try {
                $attrChanged = unserialize($attrChanged);
            } catch (Exception $e) {
                $attrChanged = array();
            }
        }
        if (!is_array($attrChanged)) {
            $attrChanged = array();
        }

        foreach ($product->getAttributes() as $attr) {
            $attrCode = $attr->getAttributeCode();
            if (!$product->getUdprodIsNew() && !$product->getUdprodIsQcNew()
                && !in_array($attrCode, $hideFields)
                && $product->dataHasChangedFor($attrCode)
                && false === strpos($attrCode, 'udprod_')
                && !in_array($attrCode, array('created_at','updated_at'))
            ) {
                $attrChanged[$attrCode] = sprintf('%s [%s]', $attr->getStoreLabel(), $attr->getAttributeCode());
                Mage::helper('udprod')->setNeedToUnpublish($product, 'attribute_changed');
            }
        }

        if (!$product->getStockItem() || $this->hasDataChanged($product->getStockItem())) {
            $attrChanged['stock.data'] = Mage::helper('udropship')->__('Stock Data');
            Mage::helper('udprod')->setNeedToUnpublish($product, 'stock_changed');
        }

        if ($product->getUdprodIsNew()) {
            Mage::helper('udprod')->setNeedToUnpublish($product, 'new_product');
        }

        $product->setData('udprod_attributes_changed', serialize($attrChanged));
        $product->getResource()->saveAttribute($product, 'udprod_attributes_changed');
        $product->setData('udprod_attributes_changed', $attrChanged);
    }

    public function hasDataChanged($object)
    {
        if (!$object->getOrigData()) {
            return true;
        }

        $fields = $object->getResource()->getReadConnection()->describeTable($object->getResource()->getMainTable());
        foreach (array_keys($fields) as $field) {
            if ($object->getOrigData($field) != $object->getData($field)) {
                return true;
            }
        }

        return false;
    }

    public function checkUniqueVendorSku($product, $vendor)
    {
        if (Mage::getStoreConfigFlag('udprod/general/unique_vendor_sku')
            && Mage::helper('udropship')->isUdmultiActive()
        ) {
            $udmulti = $product->getData('udmulti');
            if (empty($udmulti['vendor_sku'])) {
                Mage::throwException('Vendor SKU is empty');
            } elseif (Mage::helper('udropship/catalog')->getPidByVendorSku($udmulti['vendor_sku'], $vendor->getId(), $product->getId())) {
                Mage::throwException(Mage::helper('udropship')->__('Vendor SKU "%s" is already used', $udmulti['vendor_sku']));
            }
        }
    }

    public function processNewConfigurable($product, $vendor)
    {
        if ('configurable' == $product->getTypeId()) {
            $cfgAttrs = Mage::helper('udprod')->getTplConfigurableAttributes(
                $vendor,
                $product
            );
            if (is_array($cfgAttrs) && !empty($cfgAttrs)) {
                $cfgPos=0; foreach ($cfgAttrs as $cfgAttr) {
                    Mage::helper('udropship/catalog')->createCfgAttr($product, $cfgAttr, ++$cfgPos);
                }
            }
        }
    }

    public function processQuickCreate($prod, $isNew)
    {
        if ('configurable' != $prod->getTypeId()) return $this;
        
        $session = Mage::getSingleton('udropship/session');
        $v = Mage::getSingleton('udropship/session')->getVendor();
        $hlp = Mage::helper('udropship');
        $prHlp = Mage::helper('udprod');
        $newPids = array();
        if ('configurable' == $prod->getTypeId()) {
            $cfgFirstAttrs = $this->getCfgFirstAttributes($prod, $isNew);
            $cfgFirstAttr = $this->getCfgFirstAttribute($prod, $isNew);
            $cfgFirstAttrId = $cfgFirstAttr->getId();
            $cfgFirstAttrCode = $cfgFirstAttr->getAttributeCode();
            $existingPids = $prod->getTypeInstance(true)->getUsedProductIds($prod);
            $quickCreate = $prod->getData('_cfg_attribute/quick_create');
            $cfgPrices = $prod->getData('__cfg_prices');
            $existingQC = Mage::helper('udprod')->getEditSimpleProductData($prod);
            if (is_array($quickCreate)) {
            $allExistingQC = Mage::helper('udprod')->getEditSimpleProductData($prod, true);
            foreach ($quickCreate as $_qcKey => $qc) {
                $cfgFirstAttrKey = '';
                foreach ($cfgFirstAttrs as $__ca) {
                    $__id = $__ca->getAttributeId();
                    $__code = $__ca->getAttributeCode();
                    $cfgFirstAttrKey .= $__id.'-'.$qc[$__code].'-';
                }
                $cfgFirstAttrKey = rtrim($cfgFirstAttrKey, '-');
                if ($_qcKey == '$ROW') continue;
                $pId = @$qc['simple_id'];
                $qcMP = (array)@$qc['udmulti'];
                $qcSD = (array)@$qc['stock_data'];
                unset($qc['udmulti']);
                unset($qc['stock_data']);
                $qc['is_existing'] = @$qc['is_existing'] || $pId;
                if (!$pId && !empty($qc['sku'])) {
                    $pId = Mage::helper('udropship/catalog')->getPidBySku($qc['sku']);
                }
                if (!$pId && !empty($qcMP['vendor_sku'])) {
                    $pId = Mage::helper('udropship/catalog')->getPidByVendorSku($qcMP['vendor_sku'], $v->getId());
                }
                if (!empty($qc['is_existing']) && !$pId) continue;
                $superAttrKey = array();
                foreach ($prod->getTypeInstance(true)->getUsedProductAttributes($prod) as $cfgAttr) {
                    $superAttrKey[] = $cfgAttr->getId().'='.@$qc[$cfgAttr->getAttributeCode()];
                }
                $superAttrKey = implode('-', $superAttrKey);
                foreach ($allExistingQC as $eqcPid=>$eqcData) {
                    if ($eqcData['super_attr_key'] == $superAttrKey) {
                        $pId = $eqcPid;
                        $qc['is_existing'] = true;
                        break;
                    }
                }
                if ($pId) {
                    $newPids[] = $pId;
                }
                if (!empty($qc['is_existing']) && $pId && isset($existingQC[$pId])) {
                    $_eqc   = $existingQC[$pId];
                    $_eqcMP = (array)@$_eqc['udmulti'];
                    $_eqcSD = (array)@$_eqc['stock_data'];
                    unset($_eqc['udmulti']);
                    unset($_eqc['stock_data']);
                    $qcNoChanges = true;
                    foreach ($qc as $_k=>$_v) {
                        if ($_v != @$_eqc[$_k]) {
                            $qcNoChanges = false;
                            break;
                        }
                    }
                    foreach ($qcMP as $_k=>$_v) {
                        if ($_v != @$_eqcMP[$_k]) {
                            $qcNoChanges = false;
                            break;
                        }
                    }
                    foreach ($qcSD as $_k=>$_v) {
                        if ($_v != @$_eqcSD[$_k]) {
                            $qcNoChanges = false;
                            break;
                        }
                    }
                    if ($qcNoChanges && !$prod->getData('udprod_cfg_media_changed/'.$cfgFirstAttrKey)) {
                        continue;
                    }
                }
                $qcProdData = array();
                if (!Mage::helper('udropship')->isUdmultiActive()) {
                    $qcSD['is_in_stock'] = !isset($qcSD['is_in_stock']) ? 1 : $qcSD['is_in_stock'];
                    $qcProdData['stock_data'] = $qcSD;
                }
                try {
                    if ($pId) {
                        $qcProdData['options'] = array();
                        $qcProdData['has_options'] = false;
                        $qcProdData['required_options'] = false;
                        $qcProd = $prHlp->initProductEdit(array(
                            'id' => $pId,
                            'type_id' => 'simple',
                            'data' => $qcProdData,
                            'skip_check' => true
                        ));
                        $qcProd->uclearOptions();
                        $qcProd->setProductOptions(array());
                        $qcProd->setCanSaveCustomOptions(false);
                    } else {
                        $qcProdData['website_ids'] = $prod->getWebsiteIds();
                        $qcProdData['category_ids'] = $prod->getCategoryIds();
                        $qcProdData['options'] = array();
                        $qcProdData['has_options'] = false;
                        $qcProdData['required_options'] = false;
                        $qcProd = $prHlp->initProductEdit(array(
                            'id' => false,
                            'type_id' => 'simple',
                            'template_id' => $prod->getId(),
                            'data' => $qcProdData,
                        ));
                        $qcProd->uclearOptions();
                        $qcProd->setProductOptions(array());
                        $qcProd->setCanSaveCustomOptions(false);
                    }

                    if ($prHlp->isMyProduct($qcProd)) {
                        foreach ($this->getQuickCreateAllowedAttributes() as $_k) {
                            if (isset($qc[$_k])) {
                                $qcProd->setData($_k, $qc[$_k]);
                            }
                        }
                    }
                    $autogenerateOptions = array();
                    if (!Mage::getStoreConfigFlag('udprod/general/disable_name_check')) {
                        $ufName = $prod->formatUrlKey(@$qc['name']);
                    } else {
                        $ufName = @$qc['name'];
                    }
                    $ufName = trim($ufName);
                    foreach (Mage::helper('udprod')->getConfigurableAttributes($prod, $isNew) as $attribute) {
                        if ($attribute->getAttributeCode()!='name'||$ufName) {
                            $qcProd->setData($attribute->getAttributeCode(), @$qc[$attribute->getAttributeCode()]);
                        }
                        $value = $qcProd->getAttributeText($attribute->getAttributeCode());
                        $autogenerateOptions[] = $value;
                    }
                    if (!$pId) {
                        if (empty($qc['name']) || !$ufName || !empty($qc['name_auto'])) {
                            $autoName = $prod->getName().'-'.implode('-', $autogenerateOptions);
                            if (!Mage::getStoreConfigFlag('udprod/general/disable_name_check')) {
                                $autoName = $prod->formatUrlKey($autoName);
                            }
                            $qcProd->setName($autoName);
                        }
                        $qcProd->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
                    }

                    if (Mage::helper('udropship')->isUdmultiActive()) {
                        $qcMP['vendor_sku'] = trim(@$qcMP['vendor_sku']);
                        if (!empty($qcMP['vendor_sku_auto'])) {
                            $qcVsku = $qcMP['vendor_sku'] = Mage::helper('udropship/catalog')->getVendorSkuByPid($prod->getId(), $v->getId()).'-'.implode('-', $autogenerateOptions);
                            $qcVskuIdx = 0;
                            while (Mage::helper('udropship/catalog')->getPidByVendorSku($qcVsku, $v->getId(), $pId)) {
                                $qcVsku = $qcMP['vendor_sku'].'-'.(++$qcVskuIdx);
                            }
                            $qcMP['vendor_sku'] = $qcVsku;
                        }
                        if (Mage::getStoreConfigFlag('udprod/general/unique_vendor_sku')) {
                            if (empty($qc['vendor_sku'])) {
                                Mage::throwException('Vendor SKU is empty');
                            } elseif (Mage::helper('udropship/catalog')->getPidByVendorSku($qcMP['vendor_sku'], $v->getId(), $pId)) {
                                Mage::throwException(Mage::helper('udropship')->__('Vendor SKU "%s" is already used', $qcMP['vendor_sku']));
                            }
                        }
                    }

                    if (!$qcProd->getSku() && Mage::getStoreConfigFlag('udprod/general/auto_sku')) {
                        $__skuAuto = $__skuAuto1 = $prod->getSku().'-'.implode('-', $autogenerateOptions);
                        $__skuAutoIdx = 0;
                        while (Mage::helper('udropship/catalog')->getPidBySku($__skuAuto, $qcProd->getId())) {
                            $__skuAuto = $__skuAuto1.'-'.(++$__skuAutoIdx);
                        }
                        $qcProd->setSku($__skuAuto);
                    }

                    if (Mage::getSingleton('udprod/source')->isCfgUploadImagesSimple()) {
                        $this->processQcMediaChange($prod, $qcProd, $isNew);
                    }

                    $qcProd->setData('_allow_use_renamed_image', true);
                    $qcProd->setUdprodIsQcNew(!$qcProd->getId());
                    $qcProd->save();
                    $this->processAfterSave($qcProd);
                    if ($qcProd->getUdprodNeedToUnpublish()) {
                        $this->addUnpublishPids($prod, array($qcProd->getId()));
                    }

                    if (Mage::helper('udropship')->isUdmultiActive()) {
                        Mage::getResourceSingleton('udropship/helper')->insertIgnore(
                            'udropship/vendor_product',
                            array('vendor_id'=>$v->getId(), 'product_id'=>$qcProd->getId(),'status'=>Mage::helper('udmulti')->getDefaultMvStatus())
                        );
                        $udmultiUpdate = $qcMP;
                        $udmultiUpdate['isNewFlag'] = $isNew;
                        Mage::helper('udropship')->processDateLocaleToInternal(
                            $udmultiUpdate,
                            array('special_from_date','special_to_date')
                        );
                        Mage::helper('udmulti')->saveThisVendorProductsPidKeys(
                            array($qcProd->getId()=>$udmultiUpdate), $v
                        );
                    }
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    continue;
                }
                $newPids[] = $qcProd->getId();
            }
            }
            $delSimplePids = array_diff($existingPids, $newPids);
            $addSimplePids = array_diff($newPids, $existingPids);

            foreach ($addSimplePids as $addSimplePid) {
                Mage::helper('udropship/catalog')->linkCfgSimple($prod->getId(), $addSimplePid, true);
            }

            if (!empty($addSimplePids)) {
                $this->_addCfgSimplesDescrData($prod, $isNew, $addSimplePids, 'udprod_cfg_simples_added');
            }
            if (!empty($delSimplePids)) {
                $this->_addCfgSimplesDescrData($prod, $isNew, $delSimplePids, 'udprod_cfg_simples_removed');
            }

            $this->_processCfgPrices($prod, $cfgPrices);

            $delProd = Mage::getModel('catalog/product');
            foreach ($delSimplePids as $delSimplePid) {
                Mage::helper('udropship/catalog')->unlinkCfgSimple($prod->getId(), $delSimplePid, true);
                $delProd->setId($delSimplePid)->delete();
            }
            if (!empty($delSimplePids)) {
                Mage::helper('udprod')->setNeedToUnpublish($prod, 'cfg_simple_removed');
            }
            if (!empty($addSimplePids)) {
                Mage::helper('udprod')->setNeedToUnpublish($prod, 'cfg_simple_added');
            }
            $reindexPids = array_merge($newPids, $existingPids);
            $reindexPids[] = $prod->getId();
            Mage::helper('udprod')->processCfgPriceChanges($prod, $reindexPids);
            Mage::helper('udprod')->addReindexPids($prod, $reindexPids);
        }
    }

    protected function _processCfgPrices($prod, $cfgPrices)
    {
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $_priceTable = $rHlp->getTable('catalog/product_super_attribute_pricing');
        $conn = $rHlp->getWriteConnection();

        $psAttrIdSel = $conn->select()->from(array('sa'=>$rHlp->getTable('catalog/product_super_attribute')), array('attribute_id','product_super_attribute_id'));
        $psAttrIdSel->where('sa.product_id=?', (int)$prod->getId());
        $_psAttrIds = $conn->fetchAll($psAttrIdSel);
        $psAttrIds = array();
        foreach ($_psAttrIds as $_psAttrId) {
            $psAttrIds[$_psAttrId['attribute_id']] = $_psAttrId['product_super_attribute_id'];
        }

        $cfSel = $conn->select()->from(array('sa'=>$rHlp->getTable('catalog/product_super_attribute')), array('attribute_id'))
            ->join(array('sap'=>$rHlp->getTable('catalog/product_super_attribute_pricing')), 'sa.product_super_attribute_id=sap.product_super_attribute_id', array('value_id','value_index','is_percent','pricing_value'));
        $cfSel->where('sap.website_id=0');
        $cfSel->where('sa.product_id=?', (int)$prod->getId());
        $cfSel->order('sa.product_super_attribute_id');
        $rows = $conn->fetchAll($cfSel);
        if (!is_array($rows)) {
            $rows = array();
        }
        $delValueIds = array();
        foreach ($rows as $row) {
            $exists = false;
            foreach ($cfgPrices as $cfgPrice) {
                if ($cfgPrice['attribute_id']==$row['attribute_id']
                    && $cfgPrice['value_index']==$row['value_index']
                ) {
                    $exists = 1;
                    break;
                }
            }
            if (!$exists) {
                $delValueIds[] = $row['value_id'];
            }
        }
        $insert = array();
        foreach ($cfgPrices as $cfgPrice) {
            if (empty($cfgPrice['attribute_id'])) continue;
            if (empty($cfgPrice['value_index'])) continue;
            if (empty($cfgPrice['pricing_value'])) continue;
            if (!isset($psAttrIds[$cfgPrice['attribute_id']])) continue;
            $insert[] = array(
                'product_super_attribute_id' => $psAttrIds[$cfgPrice['attribute_id']],
                'value_index'=>$cfgPrice['value_index'],
                'pricing_value'=>$cfgPrice['pricing_value'],
                'is_percent'=>$cfgPrice['is_percent'],
                'website_id'=>0,
            );
        }
        if (!empty($delValueIds)) {
            $conn->delete($_priceTable, array('value_id in (?)' => $delValueIds));
        }
        if (!empty($insert)) {
            $rHlp->multiInsertOnDuplicate($_priceTable, $insert);
        }
    }

    protected function _addCfgSimplesDescrData($prod, $isNew, $simplePids, $descrAttr)
    {
        $cfgSimplesDescrDataCol = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('name','sku'));
        foreach (Mage::helper('udprod')->getConfigurableAttributes($prod, $isNew) as $cfgAttr) {
            $cfgSimplesDescrDataCol->addAttributeToSelect($cfgAttr->getAttributeCode());
        }
        $cfgSimplesDescrDataCol->addIdFilter($simplePids);

        $simplesDescrData = array();
        foreach ($cfgSimplesDescrDataCol as $csdProd) {
            $csdSI = $csdProd->getStockItem();
            $siHlp = Mage::helper('cataloginventory');
            $_descrText = sprintf('id: %s; sku: %s; stock qty: %s; stock status: %s;',
                $csdProd->getId(), $csdProd->getSku(),
                $csdSI->getQty(),
                ($csdSI->getIsInStock() ? Mage::helper('udropship')->__('In Stock') : Mage::helper('udropship')->__('Out of Stock'))
            );
            foreach (Mage::helper('udprod')->getConfigurableAttributes($prod, $isNew) as $cfgAttr) {
                $_descrText .= sprintf('%s [%s]: %s;',
                    $cfgAttr->getStoreLabel(), $cfgAttr->getAttributeCode(),
                    $cfgAttr->getSource()->getOptionText($prod->getData($cfgAttr->getAttributeCode()))
                );
            }
            $simplesDescrData[$csdProd->getId()] = substr($_descrText, 0, -1);
        }
        $exisSimplesDescr = $prod->getData($descrAttr);
        if (!is_array($exisSimplesDescr)) {
            try {
                $exisSimplesDescr = unserialize($exisSimplesDescr);
            } catch (Exception $e) {
                $exisSimplesDescr = array();
            }
        }
        if (!is_array($exisSimplesDescr)) {
            $exisSimplesDescr = array();
        }
        $exisSimplesDescr = array_merge($exisSimplesDescr, $simplesDescrData);
        $prod->setData($descrAttr, serialize($exisSimplesDescr));
        $prod->getResource()->saveAttribute($prod, $descrAttr);
        $prod->setData($descrAttr, $exisSimplesDescr);
    }

    public function processQcMediaChange($prod, $qcProd, $isNew)
    {
        $cfgFirstAttrs = $this->getCfgFirstAttributes($prod, $isNew);
        $cfgFirstAttrKey = '';
        foreach ($cfgFirstAttrs as $__ca) {
            $__id = $__ca->getAttributeId();
            $__code = $__ca->getAttributeCode();
            $cfgFirstAttrKey .= $__id.'-'.$qcProd->getData($__code).'-';
        }
        $cfgFirstAttrKey = rtrim($cfgFirstAttrKey, '-');
        $mediaImgKey = sprintf('media_gallery/cfg_images/'.$cfgFirstAttrKey);
        $mediaImgValKey = sprintf('media_gallery/cfg_values/'.$cfgFirstAttrKey);
        $mediaGallery = array(
            'images' => $prod->getData($mediaImgKey),
            'values' => $prod->getData($mediaImgValKey),
        );
        if (empty($mediaGallery['images'])) {
            return $this;
        }
        $origMediaGallery = $qcProd->getOrigData('media_gallery');
        if (is_array($origMediaGallery)
            && !empty($origMediaGallery['images'])
        ) {
            $origImages = $origMediaGallery['images'];
            if(!is_array($origImages) && strlen($origImages) > 0) {
                $origImages = Mage::helper('core')->jsonDecode($origImages);
            }
            if (!is_array($origImages)) {
                $origImages = array();
            }
            $postImages = $mediaGallery['images'];
            if(!is_array($postImages) && strlen($postImages) > 0) {
                $postImages = Mage::helper('core')->jsonDecode($postImages);
            }
            if (!is_array($postImages)) {
                $postImages = array();
            }
            foreach ($postImages as &$postImg) {
                if (!empty($postImg['value_id'])) {
                    foreach ($origImages as $origImg) {
                        if ($origImg['file']==$postImg['file']) {
                            $postImg['value_id'] = $origImg['value_id'];
                            break;
                        }
                    }
                }
            }
            unset($postImg);
            $mediaGallery['images'] = Mage::helper('core')->jsonEncode($postImages);
        }
        $qcProd->setData('media_gallery', $mediaGallery);
        foreach ($qcProd->getMediaAttributes() as $_mAttr) {
            $mediaAttrKey = sprintf('media_gallery/cfg_attributes/%s/%s',
                $cfgFirstAttrKey,
                $_mAttr->getAttributeCode()
            );
            $qcProd->setData($_mAttr->getAttributeCode(), $prod->getData($mediaAttrKey));
        }
        return $this;
    }

    public function addReindexPids($product, $pIds)
    {
        $_pIds = $product->getUdprodReindexPids();
        if (!is_array($_pIds)) {
            $_pIds = array();
        }
        $product->setUdprodReindexPids(array_merge($_pIds, $pIds));
        return $this;
    }

    public function addUnpublishPids($product, $pIds)
    {
        $_pIds = $product->getUdprodUnpublishPids();
        if (!is_array($_pIds)) {
            $_pIds = array();
        }
        $product->setUdprodUnpublishPids(array_merge($_pIds, $pIds));
        return $this;
    }

    public function reindexProduct($product)
    {
        $unpublishAttrs = array(
            'status'=>Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING,
            'udprod_fix_notify' => 0,
            'udprod_approved_notify' => 0,
            'udprod_fix_notified' => 1,
            'udprod_pending_notified' => 0,
            'udprod_approved_notified' => 1,
            'udprod_fix_admin_notified' => 1,
            'udprod_pending_admin_notified' => 0,
            'udprod_approved_admin_notified' => 1,
        );
        if ($product->getUdprodIsNew()) {
            $unpublishAttrs['udprod_pending_notify'] = 2;
            $unpublishAttrs['udprod_attributes_changed'] = '';
        } elseif (!$product->getData('udprod_pending_notify')) {
            $unpublishAttrs['udprod_pending_notify'] = 1;
        }
        if ($product->getUdprodNeedToUnpublish()) {
            Mage::getModel('catalog/product_action')->getResource()->updateAttributes(
                array($product->getId()),
                $unpublishAttrs,
                0
            );
        }
        if (($unpubPids = $product->getUdprodUnpublishPids())) {
            Mage::getModel('catalog/product_action')->getResource()->updateAttributes(
                $unpubPids,
                $unpublishAttrs,
                0
            );
        }
        $pIds = $product->getUdprodReindexPids();
        if (!is_array($pIds)) {
            $pIds = array();
        }
        if (!in_array($product->getId(), $pIds)) {
            $pIds[] = $product->getId();
        }
        Mage::helper('udropship/catalog')->reindexPids($pIds);
    }

    public function processUdmultiPost($product, $vendor)
    {
        if (Mage::helper('udropship')->isUdmultiActive()) {
            $udmulti = $product->getData('udmulti');
            Mage::getResourceSingleton('udropship/helper')->insertIgnore(
                'udropship/vendor_product',
                array('vendor_id'=>$vendor->getId(), 'product_id'=>$product->getId(),'status'=>Mage::helper('udmulti')->getDefaultMvStatus())
            );
            Mage::helper('udmulti')->setReindexFlag(false);
            if (is_array($udmulti) && !empty($udmulti) ) {
                Mage::helper('udropship')->processDateLocaleToInternal(
                    $udmulti,
                    array('special_from_date','special_to_date')
                );
                Mage::helper('udmulti')->saveThisVendorProductsPidKeys(array($product->getId()=>$udmulti), $vendor);
            }
            Mage::helper('udmulti')->setReindexFlag(true);
        }
    }

    public function checkProduct($productId=null, $vendor=null)
    {
        if (null === $productId) {
            $productId = Mage::app()->getRequest()->getParam('id');
        }
        if (null === $vendor) {
            $vendor = Mage::getSingleton('udropship/session')->getVendor();
        }
        if (!is_array($productId)) {
            $productId = array($productId);
        }
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addIdFilter($productId);
        if (0&&Mage::helper('udropship')->isUdmultiActive()) {
            $collection->addAttributeToFilter('entity_id', array('in'=>$vendor->getAssociatedProductIds()));
        } else {
            $collection->addAttributeToFilter('udropship_vendor', $vendor->getId());
        }
        $collection->load();
        Mage::app()->setCurrentStore($oldStoreId);
        if (!$collection->getFirstItem()->getId()) {
            Mage::throwException('Product Not Found');
        }
        return $this;
    }

    protected function _processTplCfgAttrs(&$templateSku)
    {
        foreach ($templateSku as &$tplSku) {
            if (isset($tplSku['cfg_attributes'])) {
                if (!is_array($tplSku['cfg_attributes'])) {
                    $tplSku['cfg_attributes'] = array($tplSku['cfg_attributes']);
                }
                $tplSku['cfg_attributes'] = array_filter($tplSku['cfg_attributes']);
            }
        }
        unset($tplSku);
        return $this;
    }

    public function processTemplateSkus($vendor, $serialize=false)
    {
        $templateSku = $vendor->getData('udprod_template_sku');
        if ($serialize) {
            if (is_array($templateSku)) {
                $this->_processTplCfgAttrs($templateSku);
                $templateSku = serialize($templateSku);
            }
        } else {
            if (is_string($templateSku)) {
                $templateSku = unserialize($templateSku);
            }
            if (!is_array($templateSku)) {
                $templateSku = array();
            }
            $this->_processTplCfgAttrs($templateSku);
        }
        $vendor->setData('udprod_template_sku', $templateSku);
    }

    public function getEditSimpleProductData($prod, $all=false, $v=null)
    {
        if (!($v && ($v=Mage::helper('udropship')->getVendor($v)) && $v->getId())) {
        $v = Mage::getSingleton('udropship/session')->getVendor();
        }
        $result = array();
        $vendorData = array();
        $isUdmulti = Mage::helper('udropship')->isUdmultiActive();
        $isUdmultiPrice = Mage::helper('udropship')->isUdmultiPriceAvailable();
        $simpleProducts = $prod->getTypeInstance(true)->getUsedProducts(null, $prod);
        $simpleProductIds = $prod->getTypeInstance(true)->getUsedProductIds($prod);
        if ($isUdmulti) {
            $vCollection = Mage::helper('udmulti')->getMultiVendorData($simpleProductIds);
            foreach ($vCollection as $vp) {
                $vendorData[$vp->getProductId()][$vp->getVendorId()] = $vp->getData();
            }
        }
        $hasVsAttr = false;
        $vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
        if ($vsAttrCode && $vsAttrCode!='sku'
            && ($hasVsAttr = Mage::helper('udropship')->checkProductAttribute($vsAttrCode))
        ) {
            $vsAttr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $vsAttrCode);
        }
        $qcAttrs = $this->getQuickCreateAttributes();
        $qcAttrs = array_keys($qcAttrs);
        $extSimpleProducts = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect($qcAttrs)
            ->addAttributeToFilter(array(array(
                'attribute' => 'entity_id',
                'in' => $simpleProductIds
            )));
        if ($isUdmulti) {
            Mage::helper('udmulti')->attachMultivendorData($simpleProducts, false, true);
        }
        foreach ($simpleProducts as $simpleProd) {
            if ($extSimple = $extSimpleProducts->getItemById($simpleProd->getId())) {
                foreach ($qcAttrs as $qcAttr) {
                    $simpleProd->setData($qcAttr, $extSimple->getData($qcAttr));
                }
            }
            if (!$all && $isUdmulti && !$simpleProd->getAllMultiVendorData($v->getId())) continue;
            if (!$all && $v->getId()!=$simpleProd->getUdropshipVendor()) continue;
            $_result = array(
                'simple_id' => $simpleProd->getId(),
                'name' => $simpleProd->getName(),
                'sku' => $simpleProd->getSku(),
                'status' => $simpleProd->getStatus(),
                'weight' => $simpleProd->getWeight(),
                'is_existing' => 1,
                'price' => $simpleProd->getPrice(),
                'special_price' => $simpleProd->getSpecialPrice(),
                'special_from_date' => $simpleProd->getSpecialFromDate(),
                'special_to_date' => $simpleProd->getSpecialToDate(),
                'product'=>$simpleProd
            );
            if ($isUdmulti) {
                $udmulti = $simpleProd->getAllMultiVendorData();
                $myUdmulti = @$udmulti[$v->getId()];
                $_result['udmulti'] = $myUdmulti;
                /*
                $_result['vendor_sku'] = @$myUdmulti['vendor_sku'];
                $_result['qty'] = @$myUdmulti['stock_qty'];
                $_result['udmulti_status'] = @$myUdmulti['status'];
                $_result['udmulti_state'] = @$myUdmulti['state'];
                if ($isUdmultiPrice) {
                    if (!empty($myUdmulti['vendor_price'])) {
                        $_result['price'] = $myUdmulti['vendor_price'];
                    }
                    $_result['special_price']     = @$myUdmulti['special_price'];
                    $_result['special_from_date'] = @$myUdmulti['special_from_date'];
                    $_result['special_to_date']   = @$myUdmulti['special_to_date'];
                }
                */
            } else {
                if ($hasVsAttr
                    && isset($extSimpleProducts)
                    && ($extSimple = $extSimpleProducts->getItemById($simpleProd->getId()))
                    && $extSimple->getId()
                ) {
                    $_result[$vsAttrCode] = $extSimple->getData($vsAttrCode);
                }
                $_result['stock_data'] = $simpleProd->getStockItem()->getData();
            }
            $superAttrKey = array();
            foreach ($prod->getTypeInstance(true)->getUsedProductAttributes($prod) as $cfgAttr) {
                $_result[$cfgAttr->getAttributeCode()] = $simpleProd->getData($cfgAttr->getAttributeCode());
                $superAttrKey[] = $cfgAttr->getId().'='.$simpleProd->getData($cfgAttr->getAttributeCode());
            }
            $_result['super_attr_key'] = implode('-', $superAttrKey);
            $result[$simpleProd->getId()] = $_result;
        }
        return $result;
    }

    public function getFilteredSimpleProductData($product, $filters=array(), $filterFlag=true)
    {
        $simpleProds = array();
        $_simpleProds = Mage::helper('udprod')->getEditSimpleProductData($product);
        foreach ($_simpleProds as $simpleProd) {
            $allowUse = true;
            foreach ($filters as $fKey=>$fVal) {
                if ($filterFlag != ($fVal == $simpleProd['product']->getData($fKey))) {
                    $allowUse = false;
                    break;
                }
            }
            if ($allowUse) $simpleProds[] = $simpleProd;
        }
        return $simpleProds;
    }

    public function getCfgAttributeValues($product, $attribute, $used=null, $filters=array(), $filterFlag=true)
    {
        $cfgAttribute = $product->getResource()->getAttribute($attribute);
        $values = $cfgAttribute->getSource()->getAllOptions();
        if ($used!==null) {
            $usedValues = array();
            $simpleProds = Mage::helper('udprod')->getEditSimpleProductData($product);
            foreach ($simpleProds as $simpleProd) {
                $simpleProd = $simpleProd['product'];
                $usedValue = $simpleProd->getData($cfgAttribute->getAttributeCode());
                $allowUse = true;
                foreach ($filters as $fKey=>$fVal) {
                    if ($filterFlag != ($fVal == $simpleProd->getData($fKey))) {
                        $allowUse = false;
                        break;
                    }
                }
                if ($allowUse) $usedValues[] = $usedValue;
            }
            $usedValues = array_unique($usedValues);
            $_values = array();
            if ($used) {
                foreach ($usedValues as $usedValue) {
                    foreach ($values as $value) {
                        if ($used === ($value['value'] == $usedValue)) {
                            $_values[] = $value;
                        }
                    }
                }
            } else {
                foreach ($values as $value) {
                    if ($used === in_array($value['value'], $usedValues)) {
                        $_values[] = $value;
                    }
                }
            }
            $values = $_values;
        }
        return $values;
    }

    public function isAllowedGrouped($vendor=null, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $setId = $_setId;
        $_setId = $__setId;
        $allowedGrouped = false;
        if ($vendor==null) {
            $vendor = $this->getVendor();
        }
        if ($vendor) {
            $vTplSku = $this->getUdprodTemplateSku($vendor);
            if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['allow_grouped'])) {
                $allowedGrouped = $vTplSku[$setId]['allow_grouped'];
            }
            if (empty($allowedGrouped) && isset($vTplSku[$_setId]) && !empty($vTplSku[$_setId]['cfg_attributes'])) {
                $allowedGrouped = $vTplSku[$_setId]['allow_grouped'];
            }
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();

        if (empty($allowedGrouped) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['allow_grouped'])) {
            $allowedGrouped = $gTplSku[$setId]['allow_grouped'];
        }
        if (empty($allowedGrouped) && isset($gTplSku[$_setId]) &&  !empty($gTplSku[$_setId]['allow_grouped'])) {
            $allowedGrouped = $gTplSku[$_setId]['allow_grouped'];
        }
        return $allowedGrouped;
    }

    public function isAllowedDownloadable($vendor=null, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $setId = $_setId;
        $_setId = $__setId;
        $allowedDownloadable = false;
        if ($vendor==null) {
            $vendor = $this->getVendor();
        }
        if ($vendor) {
            $vTplSku = $this->getUdprodTemplateSku($vendor);
            if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['allow_downloadable'])) {
                $allowedDownloadable = $vTplSku[$setId]['allow_downloadable'];
            }
            if (empty($allowedDownloadable) && isset($vTplSku[$_setId]) && !empty($vTplSku[$_setId]['cfg_attributes'])) {
                $allowedDownloadable = $vTplSku[$_setId]['allow_downloadable'];
            }
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();

        if (empty($allowedDownloadable) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['allow_downloadable'])) {
            $allowedDownloadable = $gTplSku[$setId]['allow_downloadable'];
        }
        if (empty($allowedDownloadable) && isset($gTplSku[$_setId]) &&  !empty($gTplSku[$_setId]['allow_downloadable'])) {
            $allowedDownloadable = $gTplSku[$_setId]['allow_downloadable'];
        }
        return $allowedDownloadable;
    }

    public function isAllowedVirtual($vendor=null, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $setId = $_setId;
        $_setId = $__setId;
        $allowedVirtual = false;
        if ($vendor==null) {
            $vendor = $this->getVendor();
        }
        if ($vendor) {
            $vTplSku = $this->getUdprodTemplateSku($vendor);
            if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['allow_virtual'])) {
                $allowedVirtual = $vTplSku[$setId]['allow_virtual'];
            }
            if (empty($allowedVirtual) && isset($vTplSku[$_setId]) && !empty($vTplSku[$_setId]['cfg_attributes'])) {
                $allowedVirtual = $vTplSku[$_setId]['allow_virtual'];
            }
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();

        if (empty($allowedVirtual) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['allow_virtual'])) {
            $allowedVirtual = $gTplSku[$setId]['allow_virtual'];
        }
        if (empty($allowedVirtual) && isset($gTplSku[$_setId]) &&  !empty($gTplSku[$_setId]['allow_virtual'])) {
            $allowedVirtual = $gTplSku[$_setId]['allow_virtual'];
        }
        return $allowedVirtual;
    }
    public function isAllowedSimple($vendor=null, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $setId = $_setId;
        $_setId = $__setId;
        $allowedSimple = true;
        if ($vendor==null) {
            $vendor = $this->getVendor();
        }
        if ($vendor) {
            $vTplSku = $this->getUdprodTemplateSku($vendor);
            if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['disallow_simple'])) {
                $allowedSimple = !$vTplSku[$setId]['disallow_simple'];
            }
            if (!empty($allowedSimple) && isset($vTplSku[$_setId]) && !empty($vTplSku[$_setId]['cfg_attributes'])) {
                $allowedSimple = !$vTplSku[$_setId]['disallow_simple'];
            }
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();

        if (!empty($allowedSimple) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['disallow_simple'])) {
            $allowedSimple = !$gTplSku[$setId]['disallow_simple'];
        }
        if (!empty($allowedSimple) && isset($gTplSku[$_setId]) &&  !empty($gTplSku[$_setId]['disallow_simple'])) {
            $allowedSimple = !$gTplSku[$_setId]['disallow_simple'];
        }
        return $allowedSimple;
    }

    public function hasTplConfigurableAttributes($vendor=null, $setId=null)
    {
        return (bool)$this->getTplConfigurableAttributes($vendor, $setId);
    }
    public function getTplConfigurableAttributes($vendor=null, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $setId = $_setId;
        $_setId = $__setId;
        $tplCfgAttrs = array();
        if ($vendor==null) {
            $vendor = $this->getVendor();
        }
        if ($vendor) {
            $vTplSku = $this->getUdprodTemplateSku($vendor);
            if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['cfg_attributes'])) {
                $tplCfgAttrs = $vTplSku[$setId]['cfg_attributes'];
            }
            if (empty($tplCfgAttrs) && isset($vTplSku[$_setId]) && !empty($vTplSku[$_setId]['cfg_attributes'])) {
                $tplCfgAttrs = $vTplSku[$_setId]['cfg_attributes'];
            }
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();

        if (empty($tplCfgAttrs) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['cfg_attributes'])) {
            $tplCfgAttrs = $gTplSku[$setId]['cfg_attributes'];
        }
        if (empty($tplCfgAttrs) && isset($gTplSku[$_setId]) &&  !empty($gTplSku[$_setId]['cfg_attributes'])) {
            $tplCfgAttrs = $gTplSku[$_setId]['cfg_attributes'];
        }
        return $tplCfgAttrs;
    }
    public function getConfigurableAttributes($prod, $isNew)
    {
        $vendor = Mage::helper('udropship')->getVendor($prod->getUdropshipVendor());
        $usedCfgAttrs = array();
        if ($prod->getId() && !$isNew) {
            $usedCfgAttrs = $prod->getTypeInstance(true)->getUsedProductAttributes($prod);
        } else {
            $cfgAttributes = $prod->getTypeInstance(true)->getSetAttributes($prod);
            $usedCfgAttrIds = Mage::helper('udprod')->getTplConfigurableAttributes(
                $vendor,
                $prod
            );
            if (is_array($usedCfgAttrIds)) {
                foreach ($cfgAttributes as $cfgAttribute) {
                    if (false !== ($sortKey = array_search($cfgAttribute->getId(), $usedCfgAttrIds))) {
                        $usedCfgAttrs[$sortKey] = $cfgAttribute;
                    }
                }
            }
            ksort($usedCfgAttrs, SORT_NUMERIC);
        }
        return $usedCfgAttrs;
    }
    public function getCfgFirstAttributes($product, $isNew=null)
    {
        $isNew = null === $isNew ? !$product->getId() : $isNew;
        $attrs = $this->getIdentifyImageAttributes($product, $isNew);
        if (empty($attrs)) {
            $attrs[] = $this->getCfgFirstAttribute($product, $isNew);
        }
        return $attrs;
    }
    public function getCfgFirstAttribute($product, $isNew=null)
    {
        $isNew = null === $isNew ? !$product->getId() : $isNew;
        $cfgAttributes = Mage::helper('udprod')->getConfigurableAttributes($product, $isNew);
        $cfgAttribute = !empty($cfgAttributes) ? array_shift($cfgAttributes) : false;
        return $cfgAttribute;
    }
    public function getCfgFirstAttributesValueTuples($product, $pair=false)
    {
        $cfgAttributes = $this->getCfgFirstAttributes($product);
        $usedValueTuples = array();
        $simpleProds = Mage::helper('udprod')->getEditSimpleProductData($product);
        foreach ($simpleProds as $simpleProd) {
            $simpleProd = $simpleProd['product'];
            $usedValue = array();
            foreach ($cfgAttributes as $__i=>$cfgAttribute) {
                $usedValue[] = $simpleProd->getData($cfgAttribute->getAttributeCode());
            }
            $usedValueTuples[implode('-',$usedValue)] = $usedValue;
        }
        $usedValueTuples = array_values($usedValueTuples);
        $valueTuples = array();
        foreach ($usedValueTuples as $usedValue) {
            $valueTuple = array();
            foreach ($cfgAttributes as $__i=>$cfgAttribute) {
                $values = $cfgAttribute->getSource()->getAllOptions();
                foreach ($values as $value) {
                    if ($value['value']==$usedValue[$__i]) {
                        $valueTuple[$__i] = $pair ? $value : $value['value'];
                    }
                }
            }
            $valueTuples[] = $valueTuple;
        }
        return $valueTuples;
    }
    public function getCfgFirstAttributeValues($product, $used=null, $filters=array(), $filterFlag=true)
    {
        return $this->getCfgAttributeValues($product, $this->getCfgFirstAttribute($product), $used, $filters, $filterFlag);
    }
    public function getTplIdentifyImageAttributes($vendor, $setId=null)
    {
        if (null === $setId) {
            $setId = Mage::app()->getRequest()->getParam('set_id');
        }
        $tplCfgAttrs = array();
        $vTplSku = $this->getUdprodTemplateSku($vendor);
        if (isset($vTplSku[$setId]) && !empty($vTplSku[$setId]['cfg_identify_image'])) {
            $tplCfgAttrs = $vTplSku[$setId]['cfg_identify_image'];
        }
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        if (empty($tplCfgAttrs) && isset($gTplSku[$setId]) &&  !empty($gTplSku[$setId]['cfg_identify_image'])) {
            $tplCfgAttrs = $gTplSku[$setId]['cfg_identify_image'];
        }
        return $tplCfgAttrs;
    }
    public function getIdentifyImageAttributes($prod, $isNew)
    {
        $vendor = Mage::helper('udropship')->getVendor($prod->getUdropshipVendor());
        $usedCfgAttrs = array();
        if ($prod->getId() && !$isNew) {
            $_usedCfgAttrs = $prod->getTypeInstance(true)->getConfigurableAttributes($prod);
            foreach ($_usedCfgAttrs as $_usedCfgAttr) {
                if ($_usedCfgAttr->getIdentifyImage()) {
                    $usedCfgAttrs[] = $_usedCfgAttr->getProductAttribute();
                }
            }
        } else {
            $cfgAttributes = $prod->getTypeInstance(true)->getSetAttributes($prod);
            $usedCfgAttrIds = $this->getTplIdentifyImageAttributes($vendor);
            if (is_array($usedCfgAttrIds)) {
                foreach ($cfgAttributes as $cfgAttribute) {
                    if (in_array($cfgAttribute->getId(), $usedCfgAttrIds)) {
                        $usedCfgAttrs[] = $cfgAttribute;
                    }
                }
            }
        }
        return $usedCfgAttrs;
    }
    public function isMyProduct($product)
    {
        return !$product->getId()
            || $this->getVendor()->getId() == $this->getProductVendor($product)->getId();
    }
    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }
    public function getProductVendor($product)
    {
        return Mage::helper('udropship')->getVendor($product->getUdropshipVendor());
    }
    public function processCfgPriceChanges($prod, $pIds)
    {
        $priceChanges = array();
        foreach (array('price', 'special_price', 'special_from_date', 'special_to_date') as $pKey) {
            if ($prod->dataHasChangedFor($pKey)) {
                $priceChanges[$pKey] = $prod->getData($pKey);
            }
        }
        if (!empty($priceChanges)) {
            Mage::getModel('catalog/product_action')->getResource()->updateAttributes($pIds, $priceChanges, 0);
        }
        return $this;
    }

    public function getProdSetIdLabel($prod)
    {
        $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
            ->load()
            ->toOptionHash();
        return @$options[$prod->getAttributeSetId()];
    }

    public function getUseTplProdWebsiteBySetId($setId)
    {
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        $result = @$gTplSku[$_setId]['use_product_website'];
        if (!isset($gTplSku[$_setId])) {
            $result = @$gTplSku[$__setId]['use_product_website'];
        }
        return $result;
    }
    public function getUseTplProdCategoryBySetId($setId)
    {
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        $result = @$gTplSku[$_setId]['use_product_category'];
        if (!isset($gTplSku[$_setId])) {
            $result = @$gTplSku[$__setId]['use_product_category'];
        }
        return $result;
    }

    public function getDefaultWebsiteBySetId($setId)
    {
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        $result = @$gTplSku[$_setId]['website'];
        if (!isset($gTplSku[$_setId])) {
            $result = @$gTplSku[$__setId]['website'];
        }
        return $result;
    }
    public function getDefaultCategoryBySetId($setId)
    {
        $_setId = $setId;
        if ($setId instanceof Mage_Catalog_Model_Product) {
            if (!($_setId = $setId->getUdprodAttributeSetKey())) {
                $_setId = $setId->getAttributeSetId();
            }
        }
        list($__setId) = explode('-', $_setId);
        $gTplSku = $this->getGlobalTemplateSkuConfig();
        $result = @$gTplSku[$_setId]['category'];
        if (!isset($gTplSku[$_setId])) {
            $result = @$gTplSku[$__setId]['category'];
        }
        return $result;
    }
    public function getDefaultColorByImage($p)
    {
        $images = $p->getMediaGallery('images');
        $cfgFirstAttr = Mage::helper('udprod')->getCfgFirstAttribute($p);
        $mainColorValue = null;
        if (is_array($images) && $cfgFirstAttr) {
            $cfgFirstAttrId = $cfgFirstAttr->getId();
            foreach ($images as $image) {
                if (isset($image['super_attribute'][$cfgFirstAttrId])
                    && $image['file'] == $p->getThumbnail()
                ) {
                    $mainColorValue = $image['super_attribute'][$cfgFirstAttrId];
                    break;
                }
            }
        }
        return $mainColorValue;
    }

    public function getHideEditFields()
    {
        $hideFields = explode(',', Mage::getStoreConfig('udropship/microsite/hide_product_attributes'));
        $hideFields[] = 'udropship_vendor';
        //$hideFields[] = 'tier_price';
        $hideFields[] = 'gallery';
        $hideFields[] = 'media_gallery';
        $hideFields[] = 'small_image';
        $hideFields[] = 'thumbnail';
        $hideFields[] = 'image';
        $hideFields[] = 'recurring_profile';
        $hideFields[] = '';
        return $hideFields;
    }
    public function getQCNumericAttributes()
    {
        return array('weight','status','price','special_price');
    }
    public function getQCForcedNumericAttributes()
    {
        return array('status');
    }
    public function getQCSelectAttributes()
    {
        return array('status'=>0);
    }
    public function getMvNumericAttributes()
    {
        return array('vendor_cost','stock_qty','priority','shipping_price','backorders','vendor_price','status','special_price');
    }
    public function getMvForcedNumericAttributes()
    {
        return array('status');
    }
    public function getMvSelectAttributes()
    {
        return array('status'=>0,'state'=>0);
    }
    public function getSdNumericAttributes()
    {
        return array('is_in_stock','qty');
    }
    public function getSdForcedNumericAttributes()
    {
        return array('is_in_stock');
    }
    public function getSdSelectAttributes()
    {
        return array('is_in_stock'=>0);
    }
    public function getQuickCreateAttributes()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $hideFields = $this->getHideEditFields();
        $attrs = $entityType->getAttributeCollection()
            ->addFieldToFilter('is_visible', 1)
            ->setOrder('frontend_label', 'asc');
        $qcAttrs = array();
        foreach ($attrs as $a) {
            if (in_array($a->getAttributeCode(), $this->getQuickCreateAllowedAttributes())
                && !in_array($a->getAttributeCode(), $hideFields)
            ) {
                $qcAttrs[$a->getAttributeCode()] = $a;
            }
        }
        return $qcAttrs;
    }
    public function getQuickCreateAllowedAttributes()
    {
        $qcAttrCodes = array('weight','sku','name','status','price','special_price','special_from_date','special_to_date');
        $vsAttrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
        if ($vsAttrCode && $vsAttrCode!='sku'
            && ($hasVsAttr = Mage::helper('udropship')->checkProductAttribute($vsAttrCode))
        ) {
            $qcAttrCodes[] = $vsAttrCode;
        }
        return $qcAttrCodes;
    }
    public function getQuickCreateFieldsConfig()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $hideFields = $this->getHideEditFields();
        $attrs = $entityType->getAttributeCollection()
            ->addFieldToFilter('is_visible', 1)
            ->setOrder('frontend_label', 'asc');
        $editFields = array();
        $paValues = array();
        foreach ($attrs as $a) {
            if (in_array($a->getAttributeCode(), $this->getQuickCreateAllowedAttributes())
                && !in_array($a->getAttributeCode(), $hideFields)
            ) {
                $paValues['product.'.$a->getAttributeCode()] = $a->getFrontendLabel().' ['.$a->getAttributeCode().']';
            }
        }
        $editFields['product']['label'] = 'Product Attributes';
        $editFields['product']['values'] = $paValues;
        if (Mage::helper('udropship')->isUdmultiActive()) {
            $editFields['udmulti']['label'] = Mage::helper('udropship')->__('Vendor Specific Fields');
            $editFields['udmulti']['values']  = $this->getVendorEditFieldsConfig();
        } else {
            $sdValues['stock_data.qty'] = Mage::helper('udropship')->__('Stock Qty').' [stock_item.qty]';
            $sdValues['stock_data.is_in_stock'] = Mage::helper('udropship')->__('Stock Status').' [stock_item.is_in_stock]';
            $editFields['stock_data']['label'] = Mage::helper('udropship')->__('Stock Item Fields');
            $editFields['stock_data']['values']  = $sdValues;
        }
        return $editFields;
    }
    public function getEditFieldsConfig()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $hideFields = $this->getHideEditFields();
        $attrs = $entityType->getAttributeCollection()
            ->addFieldToFilter('is_visible', 1)
            ->setOrder('frontend_label', 'asc');
        $editFields = array();
        $paValues = array();
        foreach ($attrs as $a) {
            if (!in_array($a->getAttributeCode(), $hideFields)) {
                $paValues['product.'.$a->getAttributeCode()] = $a->getFrontendLabel().' ['.$a->getAttributeCode().']';
            }
        }
        $editFields['product']['label'] = 'Product Attributes';
        $editFields['product']['values'] = $paValues;

        $editFields['system']['label'] = 'System Attributes';
        $editFields['system']['values'] = array(
            'system.product_categories' => Mage::helper('udropship')->__('Categories'),
            'system.product_websites'   => Mage::helper('udropship')->__('Websites')
        );

        if (Mage::helper('udropship')->isUdmultiActive()) {
            $editFields['udmulti']['label'] = Mage::helper('udropship')->__('Vendor Specific Fields');
            $editFields['udmulti']['values']  = $this->getVendorEditFieldsConfig();
        } else {
            $sdValues['stock_data.qty'] = Mage::helper('udropship')->__('Stock Qty').' [stock_item.qty]';
            $sdValues['stock_data.is_in_stock'] = Mage::helper('udropship')->__('Stock Status').' [stock_item.is_in_stock]';
            $sdValues['stock_data.manage_stock'] = Mage::helper('udropship')->__('Manage Stock').' [stock_item.manage_stock]';
            $sdValues['stock_data.backorders'] = Mage::helper('udropship')->__('Backorders').' [stock_item.backorders]';
            $sdValues['stock_data.min_qty'] = Mage::helper('udropship')->__('Qty for Item\'s Status to Become Out of Stock').' [stock_item.min_qty]';
            $sdValues['stock_data.min_sale_qty'] = Mage::helper('udropship')->__('Minimum Qty Allowed in Shopping Cart').' [stock_item.min_sale_qty]';
            $sdValues['stock_data.max_sale_qty'] = Mage::helper('udropship')->__('Maximum Qty Allowed in Shopping Cart').' [stock_item.max_sale_qty]';
            $editFields['stock_data']['label'] = Mage::helper('udropship')->__('Stock Item Fields');
            $editFields['stock_data']['values']  = $sdValues;
        }
        return $editFields;
    }
    public function getEditFieldsConfigSelect2Json()
    {
        $fConfig = $this->getEditFieldsConfig();
        $fRes = array(array('id'=>'','text'=>Mage::helper('udropship')->__('* Please select')));
        foreach ($fConfig as $efc) {
            if (!is_array($efc['values'])) continue;
            $_fRes = array(
                'text' => $efc['label']
            );
            foreach ($efc['values'] as $fId=>$fLbl) {
                $_fRes['children'][] = array(
                    'id' => $fId,
                    'text' => $fLbl,
                );
            }
            $fRes[] = $_fRes;
        }
        return Mage::helper('core')->jsonEncode($fRes);
    }
    public function getVendorEditFieldsConfig()
    {
        $udmHlp = Mage::helper('udmulti');
        $udmv['udmulti.vendor_sku']        = Mage::helper('udropship')->__('Vendor SKU ').' [udmulti.vendor_sku]';
        $udmv['udmulti.stock_qty']         = Mage::helper('udropship')->__('Vendor Stock Qty ').' [udmulti.stock_qty]';
        $udmv['udmulti.vendor_cost']       = Mage::helper('udropship')->__('Vendor Cost ').' [udmulti.vendor_cost]';
        $udmv['udmulti.status']            = Mage::helper('udropship')->__('Vendor Status ').' [udmulti.status]';
        $udmv['udmulti.backorders']        = Mage::helper('udropship')->__('Vendor Backorders ').' [udmulti.backorders]';
        if (Mage::helper('udropship')->isUdmultiPriceAvailable()) {
        $udmv['udmulti.vendor_price']      = Mage::helper('udropship')->__('Vendor Price ').' [udmulti.vendor_price]';
        $udmv['udmulti.group_price']       = Mage::helper('udropship')->__('Vendor Group Price ').' [udmulti.group_price]';
        $udmv['udmulti.tier_price']        = Mage::helper('udropship')->__('Vendor Tier Price ').' [udmulti.tier_price]';
        $udmv['udmulti.special_price']     = Mage::helper('udropship')->__('Vendor Special Price ').' [udmulti.special_price]';
        $udmv['udmulti.special_from_date'] = Mage::helper('udropship')->__('Vendor Special From Date ').' [udmulti.special_from_date]';
        $udmv['udmulti.special_to_date']   = Mage::helper('udropship')->__('Vendor Special To Date ').' [udmulti.special_to_date]';
        $udmv['udmulti.freeshipping']      = Mage::helper('udropship')->__('Vendor Free Shipping ').' [udmulti.freeshipping]';
        $udmv['udmulti.shipping_price']    = Mage::helper('udropship')->__('Vendor Shipping Price ').' [udmulti.shipping_price]';
        $udmv['udmulti.state']             = Mage::helper('udropship')->__('Vendor State(Condition) ').' [udmulti.state]';
        $udmv['udmulti.state_descr']       = Mage::helper('udropship')->__('Vendor State Description ').' [udmulti.state_descr]';
        $udmv['udmulti.vendor_title']      = Mage::helper('udropship')->__('Vendor Title ').' [udmulti.vendor_title]';
        }
        return $udmv;
    }

    public function setNeedToUnpublish($product, $action)
    {
        $v = Mage::getSingleton('udropship/session')->getVendor();
        $unpublishActions = Mage::getStoreConfig('udprod/general/unpublish_actions');
        if ($v->getData('is_custom_udprod_unpublish_actions')) {
            $unpublishActions = $v->getData('udprod_unpublish_actions');
        }
        if (!is_array($unpublishActions)) {
            $unpublishActions = array_filter(explode(',', $unpublishActions));
        }
        if ((empty($unpublishActions) || in_array($action, $unpublishActions) || in_array('all', $unpublishActions))
            && !in_array('none', $unpublishActions)
            || $product->getStatus()==Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX
            && in_array($action, array('attribute_changed','image_added'))
        ) {
            $product->setUdprodNeedToUnpublish(true);
        }
    }

    public function isAllowedTypeOfProduct($typeOfProduct, $vendor=null)
    {
        if ($vendor==null) {
            $vendor = Mage::getSingleton('udropship/session')->getVendor();
        }
        $at = Mage::getStoreConfig('udprod/general/allowed_types');
        if ($vendor->getData('is_custom_udprod_allowed_types')) {
            $at = $vendor->getData('udprod_allowed_types');
        }
        if (!is_array($at)) {
            $at = array_filter(explode(',', $at));
        }
        return (empty($at) || in_array($typeOfProduct, $at) || in_array('*all*', $at))
            && !in_array('*none*', $at);
    }

    public function isPendingNotifyVendor()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_pending_notifications');
    }
    public function isPendingNotifyAdmin()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_pending_admin_notifications');
    }

    public function isApprovedNotifyVendor()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_approved_notifications');
    }
    public function isApprovedNotifyAdmin()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_approved_admin_notifications');
    }
    public function isFixNotifyVendor()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_fix_notifications');
    }
    public function isFixNotifyAdmin()
    {
        return Mage::getStoreConfigFlag('udprod/notification/send_fix_admin_notifications');
    }

    public function sendPendingNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isPendingNotifyVendor() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/pending.phtml')
                ->setProducts($products)
                ->toHtml();
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/pending_vendor_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                $vendor->getEmail(),
                $vendor->getVendorName(),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_pending_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }
    public function sendApprovedNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isApprovedNotifyVendor() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/approved.phtml')
                ->setProducts($products)
                ->toHtml();
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/approved_vendor_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                $vendor->getEmail(),
                $vendor->getVendorName(),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_approved_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }
    public function sendFixNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isFixNotifyVendor() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/fix.phtml')
                ->setProducts($products)
                ->toHtml();
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/fix_vendor_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                $vendor->getEmail(),
                $vendor->getVendorName(),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_fix_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }

    public function sendPendingAdminNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isPendingNotifyAdmin() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/pending.phtml')
                ->setProducts($products)
                ->toHtml();
            $adminIdent = $store->getConfig('udprod/notification/admin_email_identity');
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/pending_admin_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_pending_admin_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }
    public function sendApprovedAdminNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isApprovedNotifyAdmin() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/approved.phtml')
                ->setProducts($products)
                ->toHtml();
            $adminIdent = $store->getConfig('udprod/notification/admin_email_identity');
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/approved_vendor_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_approved_admin_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }
    public function sendFixAdminNotificationEmail($products, $vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        if ($this->isFixNotifyAdmin() && !empty($products)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $data = array(
                'store' => $store,
                'store_name' => $store->getName(),
                'vendor'      => $vendor,
                'vendor_name' => $vendor->getVendorName(),
                'vendor_email' => $vendor->getEmail(),
            );
            $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
                ->setTemplate('unirgy/udprod/notification/fix.phtml')
                ->setProducts($products)
                ->toHtml();
            $adminIdent = $store->getConfig('udprod/notification/admin_email_identity');
            $tpl->sendTransactional(
                $store->getConfig('udprod/notification/fix_vendor_email_template'),
                $store->getConfig('udprod/notification/vendor_email_identity'),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
                $data
            );
            if ($tpl->getSentSuccess()) {
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes(array_keys($products), array('udprod_fix_admin_notified' => 1), 0);
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }

}
