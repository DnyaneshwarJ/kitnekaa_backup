<?php

class Unirgy_DropshipVendorProduct_Model_Observer
{
    public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        /*
        $block->addTab('udprod', array(
            'label'     => Mage::helper('udropship')->__('Template SKUs'),
            'after'     => 'shipping_section',
            'content'   => Mage::app()->getLayout()->createBlock('udprod/adminhtml_vendorEditTab_templateSku_form', 'vendor.udprod.form')
                ->toHtml()
        ));
        */
    }
    public function udropship_vendor_load_after($observer)
    {
        Mage::helper('udprod')->processTemplateSkus($observer->getVendor());
    }
    public function udropship_vendor_save_after($observer)
    {
        Mage::helper('udprod')->processTemplateSkus($observer->getVendor());
    }
    public function udropship_vendor_save_before($observer)
    {
        Mage::helper('udprod')->processTemplateSkus($observer->getVendor(), true);
    }
    public function core_block_abstract_prepare_layout_after($observer)
    {
        $block = $observer->getBlock();
        if ($block->getTemplate()=='media/uploader.phtml') {
            $block->setTemplate('udprod/mediaUploader.phtml');
        }
        if (!$block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
            && !$block instanceof Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs
        ) {
            return;
        }
    }
    public function controller_action_layout_load_before($observer)
    {
        if ($observer->getAction()
            && in_array($observer->getAction()->getFullActionName(), array('catalog_product_view','checkout_cart_configure'))
        ) {
            if (Mage::getStoreConfigFlag('udprod/general/use_product_zoom')) {
                $observer->getAction()->getLayout()->getUpdate()->addHandle('_udprod_product_zoom');
                if ((($p = Mage::registry('current_product'))
                    || ($p = Mage::registry('product')))
                    && $p->getTypeId()=='configurable'
                ) {
                    $observer->getAction()->getLayout()->getUpdate()->addHandle('_udprod_product_zoom_configurable');
                }
            } else {
                if ((($p = Mage::registry('current_product'))
                        || ($p = Mage::registry('product')))
                    && $p->getTypeId()=='configurable'
                ) {
                    if (Mage::getStoreConfigFlag('udprod/general/use_configurable_preselect')) {
                        $observer->getAction()->getLayout()->getUpdate()->addHandle('_udprod_product_configurable_preselect');
                    } else {
                        $observer->getAction()->getLayout()->getUpdate()->addHandle('_udprod_product_configurable');
                    }
                }
            }
        }
    }
    public function controller_front_init_before($observer)
    {
        $this->_initConfigRewrites();
    }
    public function udropship_init_config_rewrites()
    {
        $this->_initConfigRewrites();
    }
    protected function _initConfigRewrites()
    {
        if (Mage::helper('udropship')->isOSPActive()) {
            if (Mage::helper('udropship')->compareMageVer('1.5.0','1.10.0')) {
                Mage::getConfig()->setNode('global/models/catalog/rewrite/product_type_simple', 'Unirgy_DropshipVendorProduct_Model_ProductType_Simple15');
            }
        }
        if (Mage::getStoreConfigFlag('udprod/general/use_product_zoom')) {
            if (Mage::helper('udropship')->isOSPActive()) {
                Mage::getConfig()->setNode('global/models/catalog/rewrite/product_type_configurable', 'Unirgy_DropshipVendorProduct_Model_ProductTypeConfigurableOSP');
                Mage::getConfig()->setNode('global/blocks/catalog/rewrite/product_view_type_configurable', 'Unirgy_DropshipVendorProduct_Block_ProductViewTypeConfigurableOSP');
                Mage::getConfig()->setNode('global/blocks/catalog/rewrite/product_view_media', 'Unirgy_DropshipVendorProduct_Block_ProductViewMediaOSP');
            } else {
                Mage::getConfig()->setNode('global/blocks/catalog/rewrite/product_view_media', 'Unirgy_DropshipVendorProduct_Block_ProductViewMedia');
                Mage::getConfig()->setNode('global/blocks/catalog/rewrite/product_view_type_configurable', 'Unirgy_DropshipVendorProduct_Block_ProductViewTypeConfigurable');
            }
        } else {
            if (Mage::helper('udropship')->isOSPActive()) {
                Mage::getConfig()->setNode('global/models/catalog/rewrite/product_type_configurable', 'Unirgy_DropshipVendorProduct_Model_ProductTypeConfigurableOSP2');
            }
        }
        foreach (array(
            array(
                'udprod_manage_stock'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK,
                'udprod_backorders'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS,
            ),
            array(
                'udprod_min_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY,
                'udprod_min_sale_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_SALE_QTY,
                'udprod_max_sale_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY,
            ),
        ) as $isFloat=>$cfgKeyMap) {
            foreach ($cfgKeyMap as $vKey=>$cfgPath) {
                if ($isFloat) {
                    Mage::getConfig()->setNode('global/udropship/vendor/fields/'.$vKey.'/default',
                        (float)Mage::getStoreConfig($cfgPath));
                } else {
                    Mage::getConfig()->setNode('global/udropship/vendor/fields/'.$vKey.'/default',
                        (int)Mage::getStoreConfig($cfgPath));
                }
            }
        }
        Mage::getConfig()->setNode('global/udropship/vendor/fields/udprod_manage_stock/default',
            (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK));
        Mage::getConfig()->setNode('global/udropship/vendor/fields/udprod_backorders/default',
            (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS));
        Mage::getConfig()->setNode('global/udropship/vendor/fields/udprod_min_qty/default',
            (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY));
        Mage::getConfig()->setNode('global/udropship/vendor/fields/udprod_min_sale_qty/default',
            (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_SALE_QTY));
        Mage::getConfig()->setNode('global/udropship/vendor/fields/udprod_max_sale_qty/default',
            (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY));
    }
    public function catalog_product_edit_action($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $vendor = Mage::helper('udropship')->getVendor($product->getUdropshipVendor());
        if ($vendor && $vendor->getId()) {
            foreach (array(
                array(
                'udprod_manage_stock'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK,
                'udprod_backorders'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS,
                ),
                array(
                'udprod_min_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY,
                'udprod_min_sale_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_SALE_QTY,
                'udprod_max_sale_qty'=>Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY,
                ),
            ) as $isFloat=>$cfgKeyMap) {
                foreach ($cfgKeyMap as $vKey=>$cfgPath) {
                    if ($vendor->getData('is_'.$vKey)) {
                    foreach (array(
                        Mage::app()->getStore(),
                        Mage::app()->getStore(0),
                    ) as $store) {
                        if ($isFloat) {
                            $store->setConfig($cfgPath, (float)$vendor->getData($vKey));
                            Mage::getConfig()->setNode('default/'.$cfgPath, (float)$vendor->getData($vKey));
                        } else {
                            $store->setConfig($cfgPath, (int)$vendor->getData($vKey));
                            Mage::getConfig()->setNode('default/'.$cfgPath, (int)$vendor->getData($vKey));
                        }
                    }}
                }
            }
        }
    }
    public function sales_quote_config_get_product_attributes($observer)
    {
        $attributes = $observer->getAttributes()->getData();
        $res = Mage::getSingleton('core/resource');
        $conn = $res->getConnection('core_read');
        $cfgAttrIds = $conn->fetchCol(
            $conn->select()->from($res->getTableName('catalog/product_super_attribute'), 'attribute_id')->distinct(true)
        );
        $cfgAttrs = $conn->fetchPairs(
            $conn->select()->from(array('ea' => $res->getTableName('eav/attribute')), array('attribute_code', 'attribute_id'))
                ->where('attribute_id in (?)', $cfgAttrIds)
        );
        if (!empty($cfgAttrs)) {
            $observer->getAttributes()->addData($cfgAttrs);
        }
    }
    public function sales_quote_load_after($observer)
    {
        $hl = Mage::helper('udropship');
        $quote = $observer->getQuote();
        $qId = $quote->getId();
        if ($hl->isSkipQuoteLoadAfterEvent($qId) || Mage::getSingleton('udropship/observer')->getIsCartUpdateActionFlag()) return;
        $usedProducts = array();
        $cfgProducts = array();
        foreach ($quote->getAllItems() as $item) {
            if (($cpOpt = $item->getOptionByCode('cpid'))) {
                $cpId = $cpOpt->getValue();
                if (empty($usedProducts[$cpId])) {
                    $usedProducts[$cpId] = array();
                }
                $item->setName($cpOpt->getProduct()->getName());
                $cfgProducts[$cpId] = $cpOpt->getProduct();
                $usedProducts[$cpId][$item->getProduct()->getId()] = $item->getProduct();
            }
        }
        foreach ($usedProducts as $cpId => $ups) {
            if (!$cfgProducts[$cpId]->hasData('_cache_instance_products')) {
                $cfgProducts[$cpId]->setData('_cache_instance_products', $ups);
            }
        }
    }

    public function catalog_product_save_before($observer)
    {
        foreach (array('udprod_attributes_changed','udprod_cfg_simples_added','udprod_cfg_simples_removed') as $sAttr) {
            if (!$observer->getProduct()->hasData($sAttr)) {
                $observer->getProduct()->setData($sAttr, '');
            }
        }
    }

    public function catalog_product_save_commit_after($observer)
    {
        $prod = $observer->getProduct();
        if (in_array($prod->getOrigData('status'), array(Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING, Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX))
            && $prod->getData('status') == Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_ENABLED
        ) {
            $multiUpdateAttributes[$prod->getId()] = array(
                'udprod_fix_notify' => 0,
                'udprod_pending_notify' => 0,
                'udprod_approved_notify' => 1,
                'udprod_fix_notified' => 1,
                'udprod_pending_notified' => 1,
                'udprod_approved_notified' => 0,
                'udprod_fix_admin_notified' => 1,
                'udprod_pending_admin_notified' => 1,
                'udprod_approved_admin_notified' => 0,
                'udprod_attributes_changed' => '',
                'udprod_cfg_simples_added' => '',
                'udprod_cfg_simples_removed' => '',
                'udprod_fix_description' => '',
            );
        } elseif ($prod->getOrigData('status') != Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX
            && $prod->getData('status') == Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX
        ) {
            $multiUpdateAttributes[$prod->getId()] = array(
                'udprod_fix_notify' => 1,
                'udprod_pending_notify' => 0,
                'udprod_approved_notify' => 0,
                'udprod_fix_notified' => 0,
                'udprod_pending_notified' => 1,
                'udprod_approved_notified' => 1,
                'udprod_fix_admin_notified' => 0,
                'udprod_pending_admin_notified' => 1,
                'udprod_approved_admin_notified' => 1,
            );
            if ($prod->getData('udprod_pending_notify')) {
                $multiUpdateAttributes[$prod->getId()]['udprod_fix_notify'] = $prod->getData('udprod_pending_notify');
            }
        }
        if (!empty($multiUpdateAttributes)) {
            Mage::getResourceSingleton('udropship/productHelper')->multiUpdateAttributes($multiUpdateAttributes, 0);
        }
    }

    public function catalog_product_attribute_update_before($observer)
    {
        $data = $observer->getData();
        if (!empty($data['product_ids'])
            && isset($data['attributes_data']['status'])
            && in_array($data['attributes_data']['status'], array(Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX, Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_ENABLED))
        ) {
            $multiUpdateAttributes = array();
            $origProds = Mage::getModel('catalog/product')->getCollection()->addIdFilter($data['product_ids']);
            $origProds->addAttributeToSelect(array('status','udprod_pending_notify'));
            foreach ($origProds as $prod) {
                if (in_array($prod->getData('status'), array(Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING, Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX))
                    && $data['attributes_data']['status'] == Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_ENABLED
                ) {
                    $multiUpdateAttributes[$prod->getId()] = array(
                        'udprod_fix_notify' => 0,
                        'udprod_pending_notify' => 0,
                        'udprod_approved_notify' => 1,
                        'udprod_fix_notified' => 1,
                        'udprod_pending_notified' => 1,
                        'udprod_approved_notified' => 0,
                        'udprod_fix_admin_notified' => 1,
                        'udprod_pending_admin_notified' => 1,
                        'udprod_approved_admin_notified' => 0,
                        'udprod_attributes_changed' => '',
                        'udprod_cfg_simples_added' => '',
                        'udprod_cfg_simples_removed' => '',
                        'udprod_fix_description' => '',
                    );
                } elseif ($prod->getData('status') != Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX
                    && $data['attributes_data']['status'] == Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX
                ) {
                    $multiUpdateAttributes[$prod->getId()] = array(
                        'udprod_fix_notify' => 1,
                        'udprod_pending_notify' => 0,
                        'udprod_approved_notify' => 0,
                        'udprod_fix_notified' => 0,
                        'udprod_pending_notified' => 1,
                        'udprod_approved_notified' => 1,
                        'udprod_fix_admin_notified' => 0,
                        'udprod_pending_admin_notified' => 1,
                        'udprod_approved_admin_notified' => 1,
                    );
                    if ($prod->getData('udprod_pending_notify')) {
                        $multiUpdateAttributes[$prod->getId()]['udprod_fix_notify'] = $prod->getData('udprod_pending_notify');
                    }
                }
            }
            if (!empty($multiUpdateAttributes)) {
                Mage::getResourceSingleton('udropship/productHelper')->multiUpdateAttributes($multiUpdateAttributes, 0);
            }
        }
    }

    public function notifyPending()
    {
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $prods = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('sku', 'name', 'udropship_vendor', 'udprod_attributes_changed', 'udprod_cfg_simples_added', 'udprod_cfg_simples_removed'))
            ->addAttributeToFilter('status', Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_PENDING)
            ->addAttributeToFilter('udprod_pending_notify', array('gt'=>0))
            ->addAttributeToFilter('udprod_pending_notified', 0)
        ;
        $this->prepareForNotification($prods);
        $prodByVendor = array();
        foreach ($prods as $prod) {
            if (($vId = $prod->getUdropshipVendor()) && ($v = Mage::helper('udropship')->getVendor($vId)) && $v->getId()) {
                $prodByVendor[$vId][$prod->getId()] = $prod;
            }
        }
        foreach ($prodByVendor as $vId=>$vProds) {
            $v = Mage::helper('udropship')->getVendor($vId);
            Mage::helper('udprod')->sendPendingNotificationEmail($vProds, $v);
            Mage::helper('udprod')->sendPendingAdminNotificationEmail($vProds, $v);
        }
        Mage::app()->setCurrentStore($oldStoreId);
    }

    public function notifyApproved()
    {
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $prods = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('sku', 'name', 'udropship_vendor', 'udprod_attributes_changed', 'udprod_cfg_simples_added', 'udprod_cfg_simples_removed'))
            ->addAttributeToFilter('status', Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_ENABLED)
            ->addAttributeToFilter('udprod_approved_notify', array('gt'=>0))
            ->addAttributeToFilter('udprod_approved_notified', 0)
        ;
        $this->prepareForNotification($prods);
        $prodByVendor = array();
        foreach ($prods as $prod) {
            if (($vId = $prod->getUdropshipVendor()) && ($v = Mage::helper('udropship')->getVendor($vId)) && $v->getId()) {
                $prodByVendor[$vId][$prod->getId()] = $prod;
            }
        }
        foreach ($prodByVendor as $vId=>$vProds) {
            $v = Mage::helper('udropship')->getVendor($vId);
            Mage::helper('udprod')->sendApprovedNotificationEmail($vProds, $v);
            Mage::helper('udprod')->sendApprovedAdminNotificationEmail($vProds, $v);
        }
        Mage::app()->setCurrentStore($oldStoreId);
    }

    public function prepareForNotification($prods)
    {
        foreach ($prods as $prod) {
            foreach (array('udprod_attributes_changed','udprod_cfg_simples_added','udprod_cfg_simples_removed') as $descrAttr) {
                $descr = $prod->getData($descrAttr);
                if (!is_array($descr)) {
                    try {
                        $descr = unserialize($descr);
                    } catch (Exception $e) {
                        $descr = array();
                    }
                }
                if (!is_array($descr)) {
                    $descr = array();
                }
                $prod->setData($descrAttr, $descr);
            }
        }
    }

    public function notifyFix()
    {
        $oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $prods = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(array('sku', 'name', 'udropship_vendor', 'udprod_attributes_changed', 'udprod_cfg_simples_added', 'udprod_cfg_simples_removed', 'udprod_fix_description'))
            ->addAttributeToFilter('status', Unirgy_DropshipVendorProduct_Model_ProductStatus::STATUS_FIX)
            ->addAttributeToFilter('udprod_fix_notify', array('gt'=>0))
            ->addAttributeToFilter('udprod_fix_notified', 0)
        ;
        $this->prepareForNotification($prods);
        $prodByVendor = array();
        foreach ($prods as $prod) {
            if (($vId = $prod->getUdropshipVendor()) && ($v = Mage::helper('udropship')->getVendor($vId)) && $v->getId()) {
                $prodByVendor[$vId][$prod->getId()] = $prod;
            }
        }
        foreach ($prodByVendor as $vId=>$vProds) {
            $v = Mage::helper('udropship')->getVendor($vId);
            Mage::helper('udprod')->sendFixNotificationEmail($vProds, $v);
            Mage::helper('udprod')->sendFixAdminNotificationEmail($vProds, $v);
        }
        Mage::app()->setCurrentStore($oldStoreId);
    }

    public function controller_action_layout_render_before_adminhtml_system_config_edit($observer)
    {
        foreach (array(
             Mage::app()->getStore(),
             Mage::app()->getStore(0),
         ) as $store) {
            $store->setConfig('dev/js/merge_files', '0');
            Mage::getConfig()->setNode('dev/js/merge_files', '0');
        }
        Mage::app()->getLayout()->getBlock('head')->setData('udprod_can_load_select2',1);
        Mage::app()->getLayout()->getBlock('head')->removeItem('js','amasty/ambase/store.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('js','fishpig/wordpress/update.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','magemonkey/magemonkey.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/flipshop/js/jquery-1.7.2.min.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/flipshop/js/jquery.noconflict.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/flipshop/js/sm-flipshop.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/market/js/jquery-1.7.2.min.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/market/js/jquery.noconflict.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/market/js/sm-setting.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/maxshop/js/jquery-1.7.2.min.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/maxshop/js/jquery.noconflict.js');
        Mage::app()->getLayout()->getBlock('head')->removeItem('skin_js','sm/maxshop/js/sm-maxshop.js');
        if (Mage::app()->getRequest()->getParam('section')=='udprod') {
            Mage::app()->getLayout()->getBlock('content')->unsetChild('rokmage_tinymce_setup');
        }
    }

}