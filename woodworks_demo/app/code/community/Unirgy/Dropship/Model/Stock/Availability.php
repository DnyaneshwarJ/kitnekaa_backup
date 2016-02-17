<?php

class Unirgy_Dropship_Model_Stock_Availability extends Varien_Object
{
    public function alwaysAssigned($items)
    {
        // needed only for external stock check
        $this->collectStockLevels($items);
        $this->addStockErrorMessages($items, $this->getStockResult());
    }

    public function localIfInStock($items)
    {
        $iHlp = Mage::helper('udropship/item');
        $this->collectStockLevels($items, array('request_local'=>true));

        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        foreach ($items as $item) {
            if ($item->getUdropshipVendor()==$localVendorId) {
                continue;
            }
            $stock = $item->getUdropshipStockLevels();
            if (!empty($stock[$localVendorId]['status'])) {
                $iHlp->setUdropshipVendor($item, $localVendorId);
            }
        }
        $this->addStockErrorMessages($items, $this->getStockResult());
    }

    /**
    * Retrieve configuration flag whether to ship from local when in stock
    *
    * @param mixed $store
    * @param int|Unirgy_Dropship_Model_Vendor
    * @return boolean
    */
    public function getUseLocalStockIfAvailable($store=null, $vendor=null)
    {
        // if vendor is supplied
        if (!is_null($vendor)) {
            // get vendor object
            if (is_numeric($vendor)) {
                $vendor = Mage::helper('udropship')->getVendor($vendor);
            }
            $result = $vendor->getUseLocalStock();
            // if there's vendor specific configuration, use it
            if (!is_null($result) && $result!==-1) {
                return $result;
            }
        }
        // otherwise return store configuration value
        return Mage::getStoreConfig('udropship/stock/availability', $store)=='local_if_in_stock';
    }

    /**
    * Should we get the real inventory status or augmented by local stock?
    *
    * @return boolean
    */
    public function getTrueStock()
    {
        $area = Mage::getDesign()->getArea();
        $controller = Mage::app()->getRequest()->getControllerName();

        // when creating order in admin, always use the true stock status
        if (!Mage::registry('inApplyStockAvailability') && $area=='adminhtml' && !in_array($controller, array('sales_order_edit','sales_order_create'))) {
            return true;
        }
        // alwyas use trueStock if configuration says so
        if (!$this->getData('true_stock') && !$this->getUseLocalStockIfAvailable()) {
            $this->setTrueStock(true);
        }

        return $this->getData('true_stock');
    }

    /**
     * Collect stock levels for all vendors of the product
     *
     * By default retrieves stock level for assigned vendor and local, if needed
     *
     * @param mixed $items
     */
    public function collectStockLevels($items, $options=array())
    {
        $hlp = Mage::helper('udropship');
        $iHlp = Mage::helper('udropship/item');
        // get $quote and $order objects
        foreach ($items as $item) {
            if (empty($quote)) {
                $quote = $item->getQuote();
                $order = $item->getOrder();
                break;
            }
            /*
            $product = $item->getProduct();
            $pId = $item->getProductId();
            if (!$product || !$product->hasUdropshipVendor()) {
                // if not available, load full product info to get product vendor
                $product = Mage::getModel('catalog/product')->load($pId);
                $item->setData('product', $product);
            }

            $qty = $item->getQty();
            if (($options = $item->getQtyOptions()) && $qty > 0) {
                $qty = $product->getTypeInstance(true)->prepareQuoteItemQty($qty, $product);
                $item->setData('qty', $qty);
            }
            break;
            */
        }
        if (empty($quote) && empty($order)) {
            $this->setStockResult(array());
            return $this;
        }
        $store = $quote ? $quote->getStore() : $order->getStore();
        $localVendorId = Mage::helper('udropship')->getLocalVendorId($store);

        $requests = array();
        foreach ($items as $item) {
            //if ($iHlp->isVirtual($item)) continue;
            if ($item->getHasChildren() || $item->isDeleted()) {
                //$product->getTypeId()=='bundle' || $product->getTypeId()=='configurable') {
                continue;
            }
            $product = $item->getProduct();
            $pId = $item->getProductId();
            if (!$product || !$product->hasUdropshipVendor()) {
                // if not available, load full product info to get product vendor
                $product = Mage::getModel('catalog/product')->load($pId);
            }
            $vId = $product->getUdropshipVendor() ? $product->getUdropshipVendor() : $localVendorId;
            $v = $hlp->getVendor($vId);
            $sku = $product->getVendorSku() ? $product->getVendorSku() : $product->getSku();
            $requestVendors = array(
            	$vId=>array(
            		'sku'=>$sku,
                    'address_match' => $v->isAddressMatch($hlp->getAddressByItem($item)),
            		'zipcode_match' => $v->isZipcodeMatch($hlp->getZipcodeByItem($item)),
                    'country_match' => $v->isCountryMatch($hlp->getCountryByItem($item)),
            	)
            );
            if (!empty($options['request_local'])) {
                $requestVendors[$localVendorId] = array(
                	'sku'=>$product->getSku(),
                    'address_match' => $hlp->getVendor($localVendorId)->isAddressMatch($hlp->getAddressByItem($item)),
                	'zipcode_match' => $hlp->getVendor($localVendorId)->isZipcodeMatch($hlp->getZipcodeByItem($item)),
                    'country_match' => $hlp->getVendor($localVendorId)->isCountryMatch($hlp->getCountryByItem($item)),
                );
            }

            $method = $v->getStockcheckMethod() ? $v->getStockcheckMethod() : 'local';
            $cb = $v->getStockcheckCallback($method);
            if (!$cb) {
                continue;
            }
            if (empty($requests[$method])) {
                $requests[$method] = array(
                    'callback' => $cb,
                    'products' => array(),
                );
            }
            if (empty($requests[$method]['products'][$pId])) {
                $requests[$method]['products'][$pId] = array(
                    'stock_item' => $product->getStockItem(),
                    'qty_requested' => 0,
                    'vendors' => $requestVendors,
                );
            }

            $requests[$method]['products'][$pId]['qty_requested'] += $hlp->getItemStockCheckQty($item);

        }

        $iHlp->processSameVendorLimitation($items, $requests);

        $result = $this->processRequests($items, $requests);
        $this->setStockResult($result);
        return $this;
    }

    public function processRequests($items, $requests)
    {
        $hlp = Mage::helper('udropship');
        $iHlp = Mage::helper('udropship/item');
        $stock = array();
        foreach ($items as $item) {
            if (!$item->getHasChildren()/* && !$iHlp->isVirtual($item)*/) {
                $stock[$item->getProductId()] = array();
            }
        }
        foreach ($requests as $request) {
            try {
                $result = call_user_func($request['callback'], $request['products']);
            } catch (Exception $e) {
                Mage::log(__METHOD__.': '.$e->getMessage());
                continue;
            }
            if (!empty($result)) {
                foreach ($result as $pId=>$vendors) {
                    foreach ($vendors as $vId=>$v) {
                        $stock[$pId][$vId] = $v;
                    }
                }
            }
        }

        foreach ($items as $item) {
            $pId = $item->getProductId();
            $item->setUdropshipStockLevels(!empty($stock[$pId]) ? $stock[$pId] : array());
            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    $pId = $child->getProductId();
                    $child->setUdropshipStockLevels(!empty($stock[$pId]) ? $stock[$pId] : array());
                    if (!$item->isShipSeparately()) {
                        $item->setUdropshipStockLevels(!empty($stock[$pId]) ? $stock[$pId] : array());
                    }
                }
            }
        }
        
        return $stock;
    }

    public function checkLocalStockLevel($products)
    {
        $this->setTrueStock(true);
        $result = array();
        $_hlp = Mage::helper('udropship');
        $ignoreStockStatusCheck = Mage::registry('reassignSkipStockCheck');
        $ignoreAddrCheck = Mage::registry('reassignSkipAddrCheck');
        foreach ($products as $pId=>$p) {
            $stockItem = !empty($p['stock_item']) ? $p['stock_item']
                : Mage::getModel('catalog/product')->load($pId)->getStockItem();
            $status = !$stockItem->getManageStock()
                || $stockItem->getIsInStock() && $stockItem->checkQty($p['qty_requested']);
            if ($ignoreStockStatusCheck) $status = true;
            foreach ($p['vendors'] as $vId=>$dummy) {
                $zipCodeMatch = (!isset($dummy['zipcode_match']) || $dummy['zipcode_match']!==false);
                $countryMatch = (!isset($dummy['country_match']) || $dummy['country_match']!==false);
                $result[$pId][$vId]['addr_status'] = $zipCodeMatch && $countryMatch;
                if ($ignoreAddrCheck) $result[$pId][$vId]['addr_status'] = true;
                $result[$pId][$vId]['status'] = $status && $result[$pId][$vId]['addr_status'];
                $result[$pId][$vId]['zipcode_match'] = $zipCodeMatch;
                $result[$pId][$vId]['country_match'] = $countryMatch;
            }
        }
        $this->setTrueStock(false);
        return $result;
    }

    public function addStockErrorMessages($items, $stock)
    {
        $hlp = Mage::helper('udropship');
        $ciHlp = Mage::helper('cataloginventory');
        $quote = null;
        $hasOutOfStock = false;
        $allAddressMatch = true;
        $allZipcodeMatch = true;
        $allCountryMatch = true;
        foreach ($items as $item) {
            if ($item->getOrder()) {
                return $this;
            }
            $quote = $item->getQuote();
            break;
        }
        foreach ($items as $item) {
            if ($item->getHasChildren()) {
                continue;
            }
            $vendors = @$stock[$item->getProductId()];
            if (!is_array($vendors)) {
                $vendors = array();
            }
            $outOfStock = true;
            $addressMatch = true;
            $zipCodeMatch = true;
            $countryMatch = true;
            foreach ($vendors as $vId=>$v) {
                $vObj = $hlp->getVendor($vId);
                $addressMatch = $addressMatch && $vObj->isAddressMatch($hlp->getAddressByItem($item));
                $zipCodeMatch = $zipCodeMatch && $vObj->isZipcodeMatch($hlp->getZipcodeByItem($item));
                $countryMatch = $countryMatch && $vObj->isCountryMatch($hlp->getCountryByItem($item));
                if ($this->getUseLocalStockIfAvailable($quote->getStoreId(), $vId)) {
                    $outOfStock = false;
                    break;
                }
                if (!empty($v['status'])) {
                    $outOfStock = false;
                    break;
                }
            }
            $allAddressMatch = $allAddressMatch && $addressMatch;
            $allZipcodeMatch = $allZipcodeMatch && $zipCodeMatch;
            $allCountryMatch = $allCountryMatch && $countryMatch;
            if ($outOfStock && !$item->getHasError() && !$item->getMessage()) {
                $hasOutOfStock = true;
                $item->setUdmultiOutOfStock(true);
                $message = $item->getMessage() ? $item->getMessage().'<br/>' : '';
                if (!$addressMatch) {
                    $message .= Mage::helper('udropship')->__('This item is not available for your location.');
                } elseif (!$countryMatch) {
                    $message .= Mage::helper('udropship')->__('This item is not available for your country.');
                } elseif (!$zipCodeMatch ) {
                    $message .= Mage::helper('udropship')->__('This item is not available for your zipcode.');
                } else {
                    $message .= Mage::helper('udropship')->__('This product is currently out of stock.');
                }
                $item->setHasError(true)->setMessage($message);
                if ($item->getParentItem()) {
                    $item->getParentItem()->setHasError(true)->setMessage($message);
                    $qtyOptions = $item->getParentItem()->getQtyOptions();
                    if (is_array($qtyOptions)) {
                        foreach ($qtyOptions as $qtyOption) {
                            $qtyOption->setMessage($message);
                            break;
                        }
                    }
                }
            }
        }
        if ($hasOutOfStock && !$quote->getHasError() && !$quote->getMessages()) {
            if (!$allAddressMatch) {
                $message = Mage::helper('udropship')->__('Some items are not available for your location.');
            } elseif (!$allCountryMatch) {
                $message = Mage::helper('udropship')->__('Some items are not available for your country.');
            } elseif (!$allZipcodeMatch) {
                $message = Mage::helper('udropship')->__('Some items are not available for your zipcode.');
            } else {
                $message = Mage::helper('udropship')->__('Some of the products are currently out of stock');
            }
            $quote->setHasError(true)->addMessage($message);
        }
        return $this;
    }
}