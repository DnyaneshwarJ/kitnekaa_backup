<?php

class Unirgy_DropshipMultiPrice_Helper_Data extends Mage_Core_Helper_Abstract
{
    const UDMP_VENDOR_DATA_OPTION = 'udmp_vendor_data';
    public function getVendorRank($v, $price=null)
    {
        //$baseRank = $price>0?$price*10:1;
        $baseRank = 1;
        if ($v->getIsProAccount()) $baseRank = $baseRank << 1;
        if ($v->getIsCertified()) $baseRank = $baseRank << 2;
        if ($v->getIsFeatured()) $baseRank = $baseRank << 4;
        if ($v->getId()==Mage::helper('udropship')->getLocalVendorId()) $baseRank = $baseRank << 8;
        return $baseRank;
    }
    public function vendors_sort_cmp($a, $b)
    {
        $av = Mage::helper('udropship')->getVendor($a['vendor_id']);
        $bv = Mage::helper('udropship')->getVendor($b['vendor_id']);
        $avPrice = $this->_sortProduct instanceof Mage_Catalog_Model_Product
            ? $this->getVPFinalPrice($this->_sortProduct, $a)
            : $a['vendor_price'];
        $bvPrice = $this->_sortProduct instanceof Mage_Catalog_Model_Product
            ? $this->getVPFinalPrice($this->_sortProduct, $b)
            : $b['vendor_price'];
        $arank = $this->getVendorRank($av, $avPrice);
        $brank = $this->getVendorRank($bv, $bvPrice);
        return $arank > $brank ? -1 : ($arank < $brank ? 1 : ($bvPrice > $avPrice ? -1 : ($bvPrice < $avPrice ? 1 : 0)));
    }
    protected $_sortProduct;
    public function getSortedVendors($vendors, $_product=null)
    {
        $this->_sortProduct = $_product;
        @usort($vendors, array($this, 'vendors_sort_cmp'));
        $this->_sortProduct = null;
        return $vendors;
    }
    public function getVPFinalPrice($product, $vendorData)
    {
        return $this->getVendorProductFinalPrice($product, $vendorData);
    }
    public function getVendorProductFinalPrice($product, $vendorData)
    {
        Varien_Profiler::start('udmulti_getVendorProductFinalPrice');
        $this->useVendorPrice($product, $vendorData);
        $finalPrice = $product->getFinalPrice();
        $this->revertVendorPrice($product);
        Varien_Profiler::stop('udmulti_getVendorProductFinalPrice');
        return $finalPrice;
    }
    public function useVendorPrice($product, $vendorData=null)
    {
        Mage::helper('udmultiprice/protected')->useVendorPrice($product, $vendorData);
        return $this;
    }
    public function canUseVendorPrice($product)
    {
        return $product->getCustomOption('info_buyRequest');
        //return ($vendorOption = $this->getVendorOption($product)) && isset($vendorOption['vendor_price']);
    }

    public function revertVendorPrice($product)
    {
        Mage::helper('udmultiprice/protected')->revertVendorPrice($product);
        return $this;
    }
    public function getAdditionalOptions($item)
    {
        return Mage::helper('udropship/item')->getAdditionalOptions($item);
    }
    public function getItemOption($item, $code)
    {
        return Mage::helper('udropship/item')->getItemOption($item, $code);
    }
    public function saveAdditionalOptions($item, $options)
    {
        Mage::helper('udropship/item')->saveAdditionalOptions($item, $options);
        return $this;
    }
    public function saveItemOption($item, $code, $value, $serialize)
    {
        Mage::helper('udropship/item')->saveItemOption($item, $code, $value, $serialize);
        return $this;
    }
    public function deleteItemOption($item, $code, $value, $serialize)
    {
        Mage::helper('udropship/item')->deleteItemOption($item, $code);
        return $this;
    }
    public function getVendorOption($item)
    {
        $vendorOption = null;
        $vendorOption = $this->getItemOption($item, 'udmp_vendor_data');
        if (!empty($vendorOption)) {
            if (is_string($vendorOption)) {
                $vendorOption = unserialize($vendorOption);
            }
            if (!is_array($vendorOption)) {
                $vendorOption = null;
            }
        }
        return $vendorOption;
    }
    public function addBRVendorOption($item, $buyRequest=null)
    {
        $iHlp = Mage::helper('udropship/item');
        if (null === $buyRequest) {
            $buyRequest = $iHlp->getItemOption($item, 'info_buyRequest');
            if (!is_array($buyRequest)) {
                $buyRequest = unserialize($buyRequest);
            }
        }
        $brUdropshipVendor = null;
        if ($buyRequest instanceof Varien_Object
            && $buyRequest->getCode() == 'info_buyRequest'
            && $buyRequest->hasValue()
        ) {
            $buyRequest = $buyRequest->getValue();
            if (!is_array($buyRequest)) {
                $buyRequest = unserialize($buyRequest);
            }
        }
        $product = $item->getProduct();
        if ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }
        $_brVid = (int)@$buyRequest['udropship_vendor'];
        if ($_brVid) {
            $brUdropshipVendor = $_brVid;
        } else {
            $brUdropshipVendor = $product->getUdmultiBestVendor();
        }
        if ($brUdropshipVendor && $product
            && Mage::helper('udropship')->getVendor($brUdropshipVendor)->getId()
            && ($mvData = $product->getMultiVendorData($brUdropshipVendor))
            && $mvData['vendor_id'] == $brUdropshipVendor
            && false !== ($prepMvData = $this->_prepareVendorOptionData($mvData))
        ) {
            $iHlp->deleteForcedVendorIdOption($item);
            $iHlp->deleteItemOption($item, self::UDMP_VENDOR_DATA_OPTION);
            $iHlp->deleteVendorIdOption($item);
            $iHlp->setForcedVendorIdOption($item, $mvData['vendor_id']);
            $iHlp->saveItemOption($item, self::UDMP_VENDOR_DATA_OPTION, $prepMvData, true);
            $iHlp->setVendorIdOption($item, $brUdropshipVendor, true);
            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    $iHlp->setForcedVendorIdOption($child, $mvData['vendor_id']);
                }
            }
        }
    }
    public function addVendorOption($item, $vId=null)
    {
        if (null === $vId) {
            $vId = $item->getUdropshipVendor();
        }
        $iHlp = Mage::helper('udropship/item');
        $iHlp->deleteItemOption($item, self::UDMP_VENDOR_DATA_OPTION);
        $iHlp->deleteVendorIdOption($item);
        $iHlp->setVendorIdOption($item, $vId, true);
        if (Mage::helper('udropship')->getVendor($vId)->getId()
            && ($mvData = $item->getProduct()->getMultiVendorData($vId))
            && $mvData['vendor_id'] == $vId
            && false !== ($prepMvData = $this->_prepareVendorOptionData($mvData))
        ) {
            $iHlp->saveItemOption($item, self::UDMP_VENDOR_DATA_OPTION, $prepMvData, true);
        }
        return $this;
    }
    protected function _prepareVendorOptionData($data)
    {
        if (empty($data['vendor_id'])
            || !($v = Mage::helper('udropship')->getVendor($data['vendor_id']))
            || !$v->getId()
        ) {
            return false;
        }
        $vendorOption = $data;
        $vendorOption['udropship_vendor'] = $data['vendor_id'];
        $vendorOption['label'] = Mage::helper('udropship')->__('Vendor');
        $vendorOption['value'] = Mage::helper('udropship')->getVendor($data['vendor_id'])->getVendorName();
        return $vendorOption;
    }

    public function getExtCanonicState($extendedState, $returnType='code', $useDefault=false)
    {
        return Mage::getSingleton('udmultiprice/source')->getExtCanonicState($extendedState, $returnType, $useDefault);
    }
    public function getCanonicState($canonicState, $returnType='code', $useDefault=false)
    {
        return Mage::getSingleton('udmultiprice/source')->getCanonicState($canonicState, $returnType, $useDefault);
    }
    public function getExtState($extState, $returnType='code', $useDefault=false)
    {
        return Mage::getSingleton('udmultiprice/source')->getExtState($extState, $returnType, $useDefault);
    }

    public function getFullGroupedMultipriceData($product)
    {
        Varien_Profiler::start('udmulti_getFullGroupedMultipriceData');
        $simpleProducts = array();
        if ($product->getTypeId()=='configurable') {
            $simpleProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
        }
        array_unshift($simpleProducts, $product);
        Mage::helper('udmulti')->attachMultivendorData($simpleProducts, true);
        $vendors = array();
        foreach($simpleProducts as $simpleProduct) {
            $_vendors = $simpleProduct->getMultiVendorData();
            $cfgMvData = $simpleProduct->getMultiVendorData();
            if (!empty($_vendors) && is_array($_vendors)) {
                foreach ($_vendors as &$_v) {
                    foreach ($cfgMvData as $_vCfg) {
                        if ($_vCfg['vendor_id']==$_v['vendor_id']) {
                            if (!$this->isConfigurableSimplePrice()) {
                                $_v['__price_product'] = $product;
                                $_v['__price_data'] = $_vCfg;
                            } else {
                                $_v['__price_product'] = $simpleProduct;
                                $_v['__price_data'] = $_v;
                            }
                        }
                    }
                }
                unset($_v);
                $vendors = array_merge($vendors, $_vendors);
            }
        }
        Varien_Profiler::stop('udmulti_getFullGroupedMultipriceData');
        return $this->_getGroupedMultipriceData($product, $vendors);
    }
    public function getGroupedMultipriceData($product)
    {
        Varien_Profiler::start('udmulti_getGroupedMultipriceData');
        Mage::helper('udmulti')->attachMultivendorData(array($product), true);
        $vendors = $product->getMultiVendorData();
        Varien_Profiler::stop('udmulti_getGroupedMultipriceData');
        return $this->_getGroupedMultipriceData($product, $vendors);
    }
    protected function _getGroupedMultipriceData($product, $vendors)
    {
        $vendors = $this->getSortedVendors($vendors, $product);
        $canonicStatesByExt['all'] = $canonicStates['all'] = $vendorStates['all'] = array(
            'value' => 'all',
            'html_value' => 'all',
            'label' => Mage::helper('udropship')->__('All'),
            'html_label' => Mage::helper('udropship')->__('All'),
        );
        $canonicStatesPrice = array('all'=>array(null,null));
        $canonicStatesCnt = array('all'=>0);
        foreach ($vendors as $_data) {
            $_data['state'] = Mage::helper('udmultiprice')->getExtState($_data['state'], 'code', true);
            $_canonicState = Mage::helper('udmultiprice')->getExtCanonicState($_data['state'], 'pair');
            $_extState = Mage::helper('udmultiprice')->getExtState($_data['state'], 'pair');
            if (empty($_data['state']) || empty($_canonicState) || empty($_extState)) continue;
            reset($_canonicState); reset($_extState);
            $vendorStates[key($_extState)] = array(
                'value' => key($_extState),
                'html_value' => htmlspecialchars(key($_extState), ENT_QUOTES),
                'label' => current($_extState),
                'html_label' => htmlspecialchars(current($_extState), ENT_QUOTES),
            );
            $_canTmp = array(
                'value' => key($_canonicState),
                'html_value' => htmlspecialchars(key($_canonicState), ENT_QUOTES),
                'label' => current($_canonicState),
                'html_label' => htmlspecialchars(current($_canonicState), ENT_QUOTES),
            );
            $canonicStatesByExt[key($_extState)] = $_canTmp;
            $canonicStates[key($_canonicState)] = $_canTmp;
            if (empty($canonicStatesCnt[key($_canonicState)])) {
                $canonicStatesCnt[key($_canonicState)] = 1;
            } else {
                $canonicStatesCnt[key($_canonicState)]++;
            }
            if (empty($canonicStatesPrice[key($_canonicState)])) {
                $canonicStatesPrice[key($_canonicState)] = array(null,null);
            }
            $canonicStatesCnt['all']++;
            $priceData = isset($_data['__price_data'])
                ? $_data['__price_data'] : $_data;
            $priceProduct = isset($_data['__price_product'])
                ? $_data['__price_product'] : $product;
            $finalPrice = $this->getVPFinalPrice($priceProduct, $priceData);
            if ($canonicStatesPrice['all'][0]===null || $canonicStatesPrice['all'][0]>$finalPrice) {
                $canonicStatesPrice['all'][0] = $finalPrice;
            }
            if ($canonicStatesPrice['all'][1]===null || $canonicStatesPrice['all'][1]<$finalPrice) {
                $canonicStatesPrice['all'][1] = $finalPrice;
            }
            if ($canonicStatesPrice[key($_canonicState)][0]===null || $canonicStatesPrice[key($_canonicState)][0]>$finalPrice) {
                $canonicStatesPrice[key($_canonicState)][0] = $finalPrice;
            }
            if ($canonicStatesPrice[key($_canonicState)][1]===null || $canonicStatesPrice[key($_canonicState)][1]<$finalPrice) {
                $canonicStatesPrice[key($_canonicState)][1] = $finalPrice;
            }
        }
        //$pluralKeys = array('new');
        $pluralKeys = array();
        foreach ($canonicStatesCnt as $cscKey => $csc) {
            //$canonicStatesByExt[$cscKey]['orig_label'] = $canonicStatesByExt[$cscKey]['label'];
            //$canonicStatesByExt[$cscKey]['orig_html_label'] = $canonicStatesByExt[$cscKey]['html_label'];
            $canonicStates[$cscKey]['orig_label'] = $canonicStates[$cscKey]['label'];
            $canonicStates[$cscKey]['orig_html_label'] = $canonicStates[$cscKey]['html_label'];
            //$vendorStates[$cscKey]['orig_label'] = $vendorStates[$cscKey]['label'];
            //$vendorStates[$cscKey]['orig_html_label'] = $vendorStates[$cscKey]['html_label'];
            if ($csc>1 && in_array($cscKey, $pluralKeys)) {
                //$canonicStatesByExt[$cscKey]['label'] .= 's';
                //$canonicStatesByExt[$cscKey]['html_label'] .= 's';
                $canonicStates[$cscKey]['label'] .= 's';
                $canonicStates[$cscKey]['html_label'] .= 's';
                //$vendorStates[$cscKey]['label'] .= 's';
                //$vendorStates[$cscKey]['html_label'] .= 's';
            }
        }
        $result = compact('canonicStatesByExt', 'canonicStatesCnt', 'canonicStatesPrice', 'canonicStates', 'vendorStates');
        return $result;
    }
    public function hasOtherOffers($product)
    {
        $vendors = $product->getMultiVendorData();
        unset($vendors[Mage::helper('udropship')->getLocalVendorId()]);
        return count($vendors)>0;
    }

    public function isConfigurableSimplePrice()
    {
        return Mage::helper('udropship')->isModuleActive('OrganicInternet_SimpleConfigurableProducts');
    }

    public function getCfgMultiPriceDataJson($prodBlock)
    {
        return $this->getMultiPriceDataJson($prodBlock);
    }
    public function getMultiPriceDataJson($prodBlock)
    {
        $product = $prodBlock->getProduct();
        $product = !$product ? Mage::registry('current_product') : $product;
        $product = !$product ? Mage::registry('product') : $product;
        $result = array();
        $udmHlp = Mage::helper('udmulti');
        $simpleProducts = array();
        if ($product->getTypeId()=='configurable') {
            $simpleProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
        }
        array_unshift($simpleProducts, $product);
        $udmHlp->attachMultivendorData($simpleProducts, true);
        foreach ($simpleProducts as $simpleProduct) {
            $mvData = $simpleProduct->getMultiVendorData();
            $cfgMvData = $product->getMultiVendorData();
            if (!empty($mvData) && is_array($mvData) && !empty($cfgMvData) && is_array($cfgMvData)) {
                foreach ($mvData as &$_v) {
                    $_foundCfg = false;
                    foreach ($cfgMvData as $_vCfg) {
                        if ($_vCfg['vendor_id']==$_v['vendor_id']) {
                            $_foundCfg = true;
                            if (!$this->isConfigurableSimplePrice()) {
                                $_v['__price_product'] = $product;
                                $_v['__price_data'] = $_vCfg;
                            } else {
                                $_v['__price_product'] = $simpleProduct;
                                $_v['__price_data'] = $_v;
                            }
                        }
                    }
                    if (!$_foundCfg && !$this->isConfigurableSimplePrice()) {
                        $_v['__price_product'] = $product;
                        $_v['__price_data'] = array();
                    }
                }
                unset($_v);
                $simpleProduct->setMultiVendorData($mvData);
            }
            $_result = $this->prepareMultiVendorHtmlData($prodBlock, $simpleProduct);
            if ($_result) {
                foreach ($_result['mvData'] as &$__mvd) {
                    unset($__mvd['__price_product']);
                    unset($__mvd['__price_data']);
                }
                unset($__mvd);
                $result[$simpleProduct->getId()] = $_result;
            }
        }
        return Mage::helper('core')->jsonEncode($result);
    }
    public function prepareMultiVendorHtmlData($prodBlock, $product)
    {
        Varien_Profiler::start('udmulti_prepareMultiVendorHtmlData');
        $udHlp = Mage::helper('udropship');
        $mpHlp = Mage::helper('udmultiprice');
        $udmHlp = Mage::helper('udmulti');
        if (($isMicro = $udHlp->isModuleActive('umicrosite'))) {
            $msHlp = Mage::helper('umicrosite');
        }
        $mvData = $product->getMultiVendorData();
        $mvData = $mpHlp->getSortedVendors($mvData, $product);
        $gmpData = $mpHlp->getGroupedMultipriceData($product);

        foreach ($mvData as &$mv) {
            $mv['state'] = Mage::helper('udmultiprice')->getExtState($mv['state'], 'code', true);
            $mv['canonic_state'] = Mage::helper('udmultiprice')->getExtCanonicState($mv['state'], 'code', true);
            $_mv = array();
            $v = $udHlp->getVendor($mv['vendor_id']);
            $_mv['shipping_price_html'] = $this->getShippingPrice($product, $mv, 1);
            $_mv['freeshipping'] = (bool)$mv['freeshipping'];
            $_mv['is_in_stock'] = (bool)$udmHlp->isSalableByVendorData($product, $mv['vendor_id'], $mv);
            $_mv['is_certified'] = (bool)$v->getIsCertified();
            $_mv['is_featured'] = (bool)$v->getIsFeatured();
            $_mv['is_pro'] = (bool)$v->getIsProAccount();
            $_mv['vendor_name'] = $v->getVendorName();
            $_mv['review_html'] = '';
            $_mv['vendor_base_url'] = '';
            $_mv['vendor_logo'] = $v->getLogo() ? Mage::helper('udropship')->getResizedVendorLogoUrl($v, 80, 65) : '';
            if (Mage::helper('udropship')->isModuleActive('Unirgy_DropshipVendorRatings')) {
                $_mv['review_html'] = Mage::helper('udratings')->getReviewsSummaryHtml($v);
            }
            if ($isMicro && $msHlp->isAllowedAction('microsite', $v)) {
                $_mv['vendor_base_url'] = $msHlp->getVendorBaseUrl($v);
            }
            $_mv['is_allowed_microsite'] = $isMicro && $msHlp->isAllowedAction('microsite', $v);
            $priceProduct = isset($mv['__price_product'])
                ? $mv['__price_product'] : $product;
            $priceData = isset($mv['__price_data'])
                ? $mv['__price_data'] : $mv;

            $mpHlp->useVendorPrice($priceProduct, $priceData);

            $origPrice = $priceProduct->getPrice();
            $priceProduct->setFinalPrice(null);
            $optionsPrice = 0;
            if ($priceProduct->getTypeId()=='configurable') {
                $cfgAttributes = $priceProduct->getTypeInstance(true)
                    ->getConfigurableAttributes($priceProduct);
                $__custOpts = array();
                foreach ($cfgAttributes as $cfgAttribute) {
                    $__attr = $cfgAttribute->getProductAttribute();
                    $cfgAttributeId = $__attr->getId();
                    $__custOpts[$cfgAttributeId] = $product->getData($__attr->getAttributeCode());
                }
                $priceProduct->addCustomOption('attributes', serialize($__custOpts));
                $optionsPrice = $priceProduct->getPriceModel()->getTotalConfigurableItemsPrice($priceProduct, $priceProduct->getPrice());
                $priceProduct->getFinalPrice();
                $priceProduct->setPrice($priceProduct->getPrice()+$optionsPrice);
            }

            $idSuffix = sprintf('_udmp_%d', $mv['vendor_id']);

            if (Mage::helper('udropship')->compareMageVer('1.8', '1.12')) {
                $priceBlock = Mage::app()->getLayout()
                    ->createBlock('catalog/product_price')
                    ->setProduct($priceProduct);
                $_mv['price_html'] = $priceBlock->getPriceHtml($priceProduct, false, $idSuffix);
                $_mv['tier_price_html'] = $priceBlock->getTierPriceHtml();
            } else {
                $_mv['price_html'] = $prodBlock->getPriceHtml($priceProduct, false, $idSuffix);
                $_mv['tier_price_html'] = $prodBlock->getTierPriceHtml();
            }

            $_mv['idSuffix']     = $idSuffix;

            $mpHlp->revertVendorPrice($priceProduct);
            $priceProduct->setPrice($origPrice);

            $_mv['state_label'] = $gmpData['vendorStates'][$mv['state']]['html_label'];
            $mv = $_mv+$mv;
        }
        unset($mv);
        if (empty($mvData)) return false;
        $_result = array(
            'product_id' => $product->getId(),
            'grouped_multiprice_data' => $gmpData,
            'mvData' => $mvData
        );
        Varien_Profiler::stop('udmulti_prepareMultiVendorHtmlData');
        return $_result;
    }
    public function hasFreeshipping($product)
    {
        Mage::helper('udmulti')->attachMultivendorData(array($product), true);
        $mvData = $product->getMultiVendorData();
        $hasFS = false;
        if (is_array($mvData)) {
            foreach ($mvData as $mv) {
                if (@$mv['freeshipping']) {
                    $hasFS = true;
                    break;
                }
            }
        }
        return $hasFS;
    }
    public function getShippingPrice($product, $mv, $format=0)
    {
        $udHlp = Mage::helper('udropship');
        $v = $udHlp->getVendor($mv['vendor_id']);
        $_catIds = $product->getCategoryIds();
        $shippingPrice = null;
        if (!empty($mv['freeshipping'])) {
            $shippingPrice = 0;
        } elseif (null !== @$mv['shipping_price'] && '' !== @$mv['shipping_price']) {
            $shippingPrice = @$mv['shipping_price'];
        }
        if (null === $shippingPrice && !empty($_catIds)
            && Mage::helper('udropship')->isModuleActive('udtiership')
            && Mage::helper('udropship')->isModuleActive('udshipclass')
        ) {
            reset($_catIds);
            $catId = current($_catIds);
            $cats = Mage::getResourceModel('catalog/category_collection')->addIdFilter(array($catId));
            $cat = $cats->getItemById($catId);
            $catPath = explode(',', Mage::helper('udropship/catalog')->getPathInStore($cat));
            $topCatId = end($catPath);
            $topCats = Mage::helper('udropship/catalog')->getTopCategories();
            if ($topCatId && $topCats->getItemById($topCatId)) {
                $vTierShip = Mage::helper('udtiership')->getVendorTiershipRates($v);
                $gTierShip = Mage::helper('udtiership')->getGlobalTierShipConfig();
                $vscId = Mage::helper('udshipclass')->getVendorShipClass($v->getId());
                $cscId = Mage::helper('udshipclass')->getCustomerShipClass();
                $shippingPrice = Mage::helper('udtiership')->getRateToUse($vTierShip, $gTierShip, $topCatId, $vscId, $cscId, 'cost');
            }
        }
        return !$format
            ? $shippingPrice
            : ($format==1
                ? Mage::helper('core')->formatPrice($shippingPrice, false)
                : Mage::helper('core')->formatPrice($shippingPrice, true)
            );
    }
    public function attachFullPriceComparisonByState($products)
    {
        foreach ($products as $product) {
            $fgmpData = Mage::helper('udmultiprice')->getFullGroupedMultipriceData($product);
            $product->setData('FullGroupedMultipriceData', $fgmpData);
            $pcByState = @$fgmpData['canonicStatesPrice'];
            $pcByState = is_array($pcByState) ? $pcByState : array();
            $cntByState = @$fgmpData['canonicStatesCnt'];
            $cntByState = is_array($cntByState) ? $cntByState : array();
            $canonicStates = @$fgmpData['canonicStates'];
            $canonicStates = is_array($canonicStates) ? $canonicStates : array();
            $product->setData('PriceComparisonCanonicStates', $canonicStates);
            $product->setData('FullPriceComparisonByState', $pcByState);
            $product->setData('FullPriceComparisonByStateCnt', $pcByState);
            unset($pcByState['all']);
            unset($cntByState['all']);
            $product->setData('PriceComparisonByState', $pcByState);
            $product->setData('PriceComparisonByStateCnt', $cntByState);
        }
        return $this;
    }
    public function attachPriceComparisonByState($products)
    {
        foreach ($products as $product) {

            $canonicStatesByExt['all'] = $canonicStates['all'] = $vendorStates['all'] = array(
                'value' => 'all',
                'html_value' => 'all',
                'label' => Mage::helper('udropship')->__('All'),
                'html_label' => Mage::helper('udropship')->__('All'),
            );
            $canonicStatesPrice = array('all'=>array(null,null));
            $canonicStatesCnt = array('all'=>0);

            $udmpSrc = Mage::getSingleton('udmultiprice/source');
            $canStates = $udmpSrc->setPath('vendor_product_state_canonic')->toOptionHash();
            foreach ($canStates as $csKey=>$csLbl) {
                $curCnt = $product->getData('udmp_'.$csKey.'_cnt');
                $curMin = $product->getData('udmp_'.$csKey.'_min_price');
                $curMax = $product->getData('udmp_'.$csKey.'_max_price');
                if (!$curCnt) continue;
                $_canonicState = array($csKey=>$csLbl);
                $_extState = $udmpSrc->getCanonicExtStates($csKey, 'pair', true);
                if (empty($_extState)) continue;
                reset($_canonicState); reset($_extState);
                $vendorStates[key($_extState)] = array(
                    'value' => key($_extState),
                    'html_value' => htmlspecialchars(key($_extState), ENT_QUOTES),
                    'label' => current($_extState),
                    'html_label' => htmlspecialchars(current($_extState), ENT_QUOTES),
                );
                $_canTmp = array(
                    'value' => key($_canonicState),
                    'html_value' => htmlspecialchars(key($_canonicState), ENT_QUOTES),
                    'label' => current($_canonicState),
                    'html_label' => htmlspecialchars(current($_canonicState), ENT_QUOTES),
                );
                $canonicStatesByExt[key($_extState)] = $_canTmp;
                $canonicStates[key($_canonicState)] = $_canTmp;
                $canonicStatesCnt[key($_canonicState)] = $curCnt;
                $canonicStatesPrice[key($_canonicState)] = array($curMin,$curMax);
                $canonicStatesCnt['all'] += $curCnt;
                if ($canonicStatesPrice['all'][0]===null || $canonicStatesPrice['all'][0]>$curMin) {
                    $canonicStatesPrice['all'][0] = $curMin;
                }
                if ($canonicStatesPrice['all'][1]===null || $canonicStatesPrice['all'][1]<$curMax) {
                    $canonicStatesPrice['all'][1] = $curMax;
                }
            }
            $pluralKeys = array();
            foreach ($canonicStatesCnt as $cscKey => $csc) {
                //$canonicStatesByExt[$cscKey]['orig_label'] = $canonicStatesByExt[$cscKey]['label'];
                //$canonicStatesByExt[$cscKey]['orig_html_label'] = $canonicStatesByExt[$cscKey]['html_label'];
                $canonicStates[$cscKey]['orig_label'] = $canonicStates[$cscKey]['label'];
                $canonicStates[$cscKey]['orig_html_label'] = $canonicStates[$cscKey]['html_label'];
                //$vendorStates[$cscKey]['orig_label'] = $vendorStates[$cscKey]['label'];
                //$vendorStates[$cscKey]['orig_html_label'] = $vendorStates[$cscKey]['html_label'];
                if ($csc>1 && in_array($cscKey, $pluralKeys)) {
                    //$canonicStatesByExt[$cscKey]['label'] .= 's';
                    //$canonicStatesByExt[$cscKey]['html_label'] .= 's';
                    $canonicStates[$cscKey]['label'] .= 's';
                    $canonicStates[$cscKey]['html_label'] .= 's';
                    //$vendorStates[$cscKey]['label'] .= 's';
                    //$vendorStates[$cscKey]['html_label'] .= 's';
                }
            }
            $fgmpData = compact('canonicStatesByExt', 'canonicStatesCnt', 'canonicStatesPrice', 'canonicStates', 'vendorStates');

            $product->setData('FullGroupedMultipriceData', $fgmpData);
            $pcByState = @$fgmpData['canonicStatesPrice'];
            $pcByState = is_array($pcByState) ? $pcByState : array();
            $cntByState = @$fgmpData['canonicStatesCnt'];
            $cntByState = is_array($cntByState) ? $cntByState : array();
            $canonicStates = @$fgmpData['canonicStates'];
            $canonicStates = is_array($canonicStates) ? $canonicStates : array();
            $product->setData('PriceComparisonCanonicStates', $canonicStates);
            $product->setData('FullPriceComparisonByState', $pcByState);
            $product->setData('FullPriceComparisonByStateCnt', $pcByState);
            unset($pcByState['all']);
            unset($cntByState['all']);
            $product->setData('PriceComparisonByState', $pcByState);
            $product->setData('PriceComparisonByStateCnt', $cntByState);
        }
        return $this;
    }
    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }
        return new Zend_Db_Expr($expression);
    }
    public function getDatePartSql($date)
    {
        return new Zend_Db_Expr(sprintf('DATE(%s)', $date));
    }
}
