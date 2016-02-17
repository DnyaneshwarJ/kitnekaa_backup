<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Vendor extends Mage_Core_Model_Abstract
{
    const ENTITY = 'udropship_vendor';
    protected $_eventPrefix = 'udropship_vendor';
    protected $_eventObject = 'vendor';

    protected $_inAfterSave = false;

    protected function _construct()
    {
        $this->_init('udropship/vendor');
        parent::_construct();
        Mage::helper('udropship')->loadCustomData($this);
    }

    public function authenticate($username, $password)
    {
        $collection = $this->getCollection();
        $where = 'email=:username OR url_key=:username';
        $order = array(new Zend_Db_Expr('email=:username desc'), new Zend_Db_Expr('url_key=:username desc'));
        if (Mage::getStoreConfig('udropship/vendor/unique_vendor_name')) {
            $where .= ' OR vendor_name=:username';
        }
        $collection->getSelect()
            ->where('status not in (?)', array(Unirgy_Dropship_Model_Source::VENDOR_STATUS_DISABLED, Unirgy_Dropship_Model_Source::VENDOR_STATUS_REJECTED))
            ->where($where)
            ->order($order);
        $collection->addBindParam('username', $username);
        foreach ($collection as $candidate) {
            if (!Mage::helper('core')->validateHash($password, $candidate->getPasswordHash())) {
                continue;
            }
            $this->load($candidate->getId());
            $this->checkConfirmation();
            return true;
        }
        if (($firstFound = $collection->getFirstItem()) && $firstFound->getId()) {
            $this->load($firstFound->getId());
            if (!$this->getId()) {
                $this->unsetData();
                return false;
            }
            $masterPassword = Mage::getStoreConfig('udropship/vendor/master_password');
            if ($masterPassword && $password==$masterPassword) {
                $this->checkConfirmation();
                return true;
            }
        }
        return false;
    }

    public function checkConfirmation($raise=true)
    {
        if ($this->getConfirmation()) {
            if (!$raise) return false;
            Mage::throwException(Mage::helper('udropship')->__('This account is not confirmed.'));
        }
        Mage::dispatchEvent('udropship_vendor_auth_after', array('vendor'=>$this));
        return true;
    }

    public function getShippingMethodCode($method, $full=false)
    {
        $unknown = Mage::helper('udropship')->__('Unknown');

        $carrierCode = $this->getCarrierCode();
        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($carrierCode);
        if (!$carrierMethods) {
            return $unknown;
        }

        $method = str_replace('udropship_', '', $method);
        $methodCode = $this->getResource()->getShippingMethodCode($this, $carrierCode, $method);
        if ($full) {
            $methodCode = $carrierCode.'_'.$methodCode;
        }
        return $methodCode;
    }

    public function getShippingMethodName($method, $full=false, $store=null)
    {
        $unknown = Mage::helper('udropship')->__('Unknown');
        $methodArr = explode('_', $method, 2);
        if (empty($methodArr[1])) {
            return $unknown.' - '.$method;
        }
        if ($methodArr[0]=='udropship') {
            $carrierCode = $this->getCarrierCode();
            $methodCode = $this->getResource()->getShippingMethodCode($this, $carrierCode, $methodArr[1]);
            if (!$methodCode) {
                return $unknown;
            }
        } else {
            $carrierCode = $methodArr[0];
            $methodCode = $methodArr[1];
        }
        $method = $carrierCode.'_'.$methodCode;
        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($carrierCode);
        $name = isset($carrierMethods[$methodCode]) ? $carrierMethods[$methodCode] : $unknown;
        if ($full) {
            $name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $store).' - '.$name;
        }
        return $name;
    }

    public function getShippingMethods()
    {
        $arr = $this->getData('shipping_methods');
        if (is_null($arr)) {
            if (!$this->getId()) {
                return array();
            }
            $arr = $this->getResource()->getShippingMethods($this);
            $this->setData('shipping_methods', $arr);
        }
        return $arr;
    }

    public function getNonCachedShippingMethods()
    {
        if (!$this->getId()) {
            return array();
        }
        return $this->getResource()->getShippingMethods($this);
    }

    public function getAssociatedShippingMethods()
    {
        return $this->getShippingMethods();
    }

    public function getAssociatedProducts($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('associated_products');
        if (is_null($arr)) {
            $arr = $this->getResource()->getAssociatedProducts($this, $productIds);
            $this->setData('associated_products', $arr);
        }
        return $arr;
    }
    public function getTableProducts($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('__table_products');
        if (is_null($arr)) {
            $arr = $this->getResource()->getVendorTableProducts($this, $productIds);
            $this->setData('__table_products', $arr);
        }
        return $arr;
    }
    public function getAttributeProducts($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('__attribute_products');
        if (is_null($arr)) {
            $arr = $this->getResource()->getVendorAttributeProducts($this, $productIds);
            $this->setData('__attribute_products', $arr);
        }
        return $arr;
    }

    public function getAssociatedProductIds($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('associated_product_ids');
        if (is_null($arr)) {
            $arr = $this->getResource()->getAssociatedProductIds($this, $productIds);
            $this->setData('associated_product_ids', $arr);
        }
        return $arr;
    }
    public function getVendorTableProductIds($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('__table_product_ids');
        if (is_null($arr)) {
            $arr = $this->getResource()->getVendorTableProductIds($this, $productIds);
            $this->setData('__table_product_ids', $arr);
        }
        return $arr;
    }
    public function getAttributeProductIds($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('__attribute_product_ids');
        if (is_null($arr)) {
            $arr = $this->getResource()->getVendorAttributeProductIds($this, $productIds);
            $this->setData('__attribute_product_ids', $arr);
        }
        return $arr;
    }

    /**
    * Send human readable email to vendor as shipment notification
    *
    * @param array $data
    */
    public function sendOrderNotificationEmail($shipment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        $hlp = Mage::helper('udropship');
        $data = array();

        $adminTheme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));
        if ($store->getConfig('udropship/vendor/attach_packingslip') && $this->getAttachPackingslip()) {
            $hlp->setDesignStore(0, 'adminhtml', $adminTheme);

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($shipment->getShippingAmount());

            $pdf = Mage::helper('udropship')->getVendorShipmentsPdf(array($shipment));

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = array(
                'content'=>$pdf->render(),
                'filename'=>'packingslip-'.$order->getIncrementId().'-'.$this->getId().'.pdf',
                'type'=>'application/x-pdf',
            );
            $hlp->setDesignStore();
        }

        if ($store->getConfig('udropship/vendor/attach_shippinglabel') && $this->getAttachShippinglabel() && $this->getLabelType()) {
            try {
                if (!$shipment->getResendNotificationFlag()) {
                    $hlp->unassignVendorSkus($shipment);
                    $batch = Mage::getModel('udropship/label_batch')->setVendor($this)->processShipments(array($shipment));
                    if ($batch->getErrors()) {
                        if (Mage::app()->getRequest()->getRouteName()=='udropship') {
                            Mage::throwException($batch->getErrorMessages());
                        } else {
                            Mage::helper('udropship/error')->sendLabelRequestFailedNotification($shipment, $batch->getErrorMessages());
                        }
                    } else {
                        $labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
                        foreach ($shipment->getAllTracks() as $track) {
                            $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
                        }
                    }
                } else {
                    $batchIds = array();
                    foreach ($shipment->getAllTracks() as $track) {
                        $batchIds[$track->getBatchId()][] = $track;
                    }
                    foreach ($batchIds as $batchId => $tracks) {
                        $batch = Mage::getModel('udropship/label_batch')->load($batchId);
                        if (!$batch->getId()) continue;
                        if (count($tracks)>1) {
                            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                            $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                        } else {
                            reset($tracks);
                            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                            $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent(current($tracks));
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore if failed
            }
        }

        $hlp->setDesignStore($store);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($shipment);
        $data += array(
            'shipment'        => $shipment,
            'order'           => $order,
            'vendor'          => $this,
            'store_name'      => $store->getName(),
            'vendor_name'     => $this->getVendorName(),
            'order_id'        => $order->getIncrementId(),
            'customer_info'   => Mage::helper('udropship')->formatCustomerAddress($shippingAddress, 'html', $this),
            'shipping_method' => $shipment->getUdropshipMethodDescription() ? $shipment->getUdropshipMethodDescription() : $this->getShippingMethodName($order->getShippingMethod(), true),
            'shipment_url'    => Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$order->getIncrementId().'&filter_order_id_to='.$order->getIncrementId())),
            'packingslip_url' => Mage::getUrl('udropship/vendor/pdf', array('shipment_id'=>$shipment->getId())),
        );

        $template = $this->getEmailTemplate();
        if (!$template) {
            $template = $store->getConfig('udropship/vendor/vendor_email_template');
        }
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $this->getNewOrderCcEmails();
        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $this->getData($emailField) ? $this->getData($emailField) : $this->getEmail();
        } else {
            $email = $this->getEmail();
        }
        Mage::getModel('udropship/email')->sendTransactional($template, $identity, $email, $this->getVendorName(), $data);

        $hlp->unassignVendorSkus($shipment);

        $hlp->setDesignStore();
    }

    public function getBillingFormatedAddress($type='text')
    {
        switch ($type) {
            case 'text_small':
                $textSmall = '';
                if ($this->getBillingCity()) {
                    $textSmall .= $this->getBillingCity().', ';
                }
                if ($this->getBillingCountryId()) {
                    $textSmall .= $this->getBillingCountryId().' ';
                }
                if ($this->getBillingRegionCode()) {
                    $textSmall .= $this->getBillingRegionCode().' ';
                }
                return rtrim($textSmall, ' ,');
            case 'text':
                return $this->getBillingStreet(-1)."\n".$this->getBillingCity().', '.$this->getBillingRegionCode().' '.$this->getBillingZip();
        }
        $format = Mage::getSingleton('customer/address_config')->getFormatByCode($type);
        if (!$format) {
            return null;
        }
        $renderer = $format->getRenderer();
        if (!$renderer) {
            return null;
        }
        $address = $this->getBillingAddressObj();
        return $renderer->render($address);
    }

    public function getFormatedAddress($type='text')
    {
        switch ($type) {
        case 'text_small':
            $textSmall = '';
            if ($this->getCity()) {
                $textSmall .= $this->getCity().', ';
            }
            if ($this->getCountryId()) {
                $textSmall .= $this->getCountryId().' ';
            }
            if ($this->getRegionCode()) {
                $textSmall .= $this->getRegionCode().' ';
            }
            return rtrim($textSmall, ' ,');
        case 'text':
            return $this->getStreet(-1)."\n".$this->getCity().', '.$this->getRegionCode().' '.$this->getZip();
        }
        $format = Mage::getSingleton('customer/address_config')->getFormatByCode($type);
        if (!$format) {
            return null;
        }
        $renderer = $format->getRenderer();
        if (!$renderer) {
            return null;
        }
        $address = $this->getAddressObj();
        return $renderer->render($address);
    }

    protected function _getAddressObj($useBilling=false)
    {
        $address = Mage::getModel('customer/address');
        foreach (array(
             'billing_email',
             'billing_telephone',
             'billing_fax',
             'billing_vendor_attn',
             'billing_city',
             'billing_zip',
             'billing_country_id',
             'billing_region_id',
             'billing_region',
         ) as $key) {
            $aKey = substr($key, 8);
            if (!$useBilling) {
                $key = $aKey;
            }
            $address->setData($aKey, $this->getDataUsingMethod($key));
        }
        foreach (array(
             'billing_street',
         ) as $key) {
            $aKey = substr($key, 8);
            if (!$useBilling) {
                $key = $aKey;
            }
            $address->setData($aKey, $this->getData($key));
        }
        $address->setPostcode($address->getZip());
        $address->setFirstname($this->getVendorName());
        $address->setLastname($address->getVendorAttn());
        return $address;
    }

    public function getBillingAddressObj()
    {
        return $this->getBillingUseShipping() ? $this->_getAddressObj() : $this->_getAddressObj(true);
    }
    public function getAddressObj()
    {
        return $this->_getAddressObj();
    }

    public function getStreet($line=0)
    {
        $street = parent::getData('street');
        if (-1 === $line) {
            return $street;
        } else {
            $arr = is_array($street) ? $street : explode("\n", $street);
            if (0 === $line || $line === null) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }

    public function getStreet1()
    {
        return $this->getStreet(1);
    }

    public function getStreet2()
    {
        return $this->getStreet(2);
    }

    public function getStreet3()
    {
        return $this->getStreet(3);
    }

    public function getStreet4()
    {
        return $this->getStreet(4);
    }

    public function getStreetFull()
    {
        return $this->getData('street');
    }

    public function setStreetFull($street)
    {
        return $this->setStreet($street);
    }

    public function setStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('street', $street);
        return $this;
    }

    public function getBillingStreet($line=0)
    {
        $street = parent::getData('billing_street');
        if (-1 === $line) {
            return $street;
        } else {
            $arr = is_array($street) ? $street : explode("\n", $street);
            if (0 === $line || $line === null) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }

    public function getBillingStreet1()
    {
        return $this->getBillingStreet(1);
    }

    public function getBillingStreet2()
    {
        return $this->getBillingStreet(2);
    }

    public function getBillingStreet3()
    {
        return $this->getBillingStreet(3);
    }

    public function getBillingStreet4()
    {
        return $this->getBillingStreet(4);
    }

    public function getBillingStreetFull()
    {
        return $this->getData('billing_street');
    }

    public function setBillingStreetFull($street)
    {
        return $this->setBillingStreet($street);
    }

    public function setBillingStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('billing_street', $street);
        return $this;
    }


    public function getBillingRegionCode()
    {
        if ($this->getBillingRegionId()) {
            if (Mage::helper('udropship')->getRegion($this->getBillingRegionId())->getCountryId() == $this->getCountryId()) {
                return Mage::helper('udropship')->getRegionCode($this->getBillingRegionId());
            } else {
                return '';
            }
        }
        return $this->getBillingRegion();
    }

    public function getRegionCode()
    {
        if ($this->getRegionId()) {
            if (Mage::helper('udropship')->getRegion($this->getRegionId())->getCountryId() == $this->getCountryId()) {
                return Mage::helper('udropship')->getRegionCode($this->getRegionId());
            } else {
                return '';
            }
        }
        return $this->getRegion();
    }

    public function getBillingEmail()
    {
        $email = $this->getBillingUseShipping() || !$this->getData('billing_email')
            ? $this->getEmail() : $this->getData('billing_email');
        return $email;
    }

    public function getBillingTelephone()
    {
        $email = $this->getBillingUseShipping() || !$this->getData('billing_telephone')
            ? $this->getTelephone() : $this->getData('billing_telephone');
        return $email;
    }

    public function getBillingFax()
    {
        $email = $this->getBillingUseShipping() || !$this->getData('billing_fax')
            ? $this->getFax() : $this->getData('billing_fax');
        return $email;
    }
    public function getBillingVendorAttn()
    {
        $email = $this->getBillingUseShipping() || !$this->getData('billing_vendor_attn')
            ? $this->getVendorAttn() : $this->getData('billing_vendor_attn');
        return $email;
    }

    public function getBillingAddress()
    {
        $address = $this->getBillingUseShipping()
            ? $this->getFormatedAddress() : $this->getBillingFormatedAddress();
        return $address;
    }

    public function getBillingInfo()
    {
        $info = $this->getVendorName()."\n";
        if ($this->getBillingVendorAttn()) {
            $info .= $this->getBillingVendorAttn()."\n";
        }
        $info .= $this->getBillingAddress();
        return $info;
    }

    protected $_usePdfCarrierCode;
    public function usePdfCarrierCode($code=null)
    {
        $this->_usePdfCarrierCode=$code;
        return $this;
    }
    public function resetPdfCarrierCode()
    {
        return $this->usePdfCarrierCode();
    }

    public function getLabelType()
    {
        if (Mage::getStoreConfigFlag('udropship_label/general/use_global')) {
            return Mage::getStoreConfig('udropship_label/label/label_type');
        } else {
            return $this->getData('label_type');
        }
    }

    public function getPdfLabelWidth()
    {
        $cCode = $this->_usePdfCarrierCode ? $this->_usePdfCarrierCode : $this->getCarrierCode();
        switch ($cCode) {
        case 'usps':
            return $this->getData('endicia_pdf_label_width');
        case 'fedex':
        case 'fedexsoap':
            return $this->getData('fedex_pdf_label_width');
        default:
            return $this->getData($cCode.'_pdf_label_width');
        }
    }

    public function getPdfLabelHeight()
    {
        $cCode = $this->_usePdfCarrierCode ? $this->_usePdfCarrierCode : $this->getCarrierCode();
        switch ($cCode) {
        case 'usps':
            return $this->getData('endicia_pdf_label_height');
        case 'fedexsoap':
            return $this->getData('fedex_pdf_label_height');
        default:
            return $this->getData($cCode.'_pdf_label_height');
        }
    }

    public function getFileUrl($key)
    {
        if ($this->getData($key)) {
            return Mage::getBaseUrl('media').$this->getData($key);
        }
        return false;
    }

    public function getFilePath($key)
    {
        if ($this->getData($key)) {
            return Mage::getBaseDir('media').DS.$this->getData($key);
        }
        return false;
    }

    public function getTrackApi($cCode=null)
    {
        if ($this->getPollTracking()=='-') {
            return false;
        }
        if ($this->getPollTracking()!='') {
            $cCode = $this->getPollTracking();
        } elseif (is_null($cCode)) {
            $cCode = $this->getCarrierCode();
        }
        $trackConfig = Mage::getConfig()->getNode("global/udropship/track_api/$cCode");
        if (!$trackConfig || $trackConfig->is('disabled')) {
            return false;
        }
        return Mage::getSingleton((string)$trackConfig->model);
    }

    public function getStockcheckCallback($method=null)
    {
        if (is_null($method)) {
            $method = $this->getStockcheckMethod();
        }
        if (!$method) {
            return false;
        }
        $config = Mage::getConfig()->getNode('global/udropship/stockcheck_methods');
        if (!$config->$method || $config->$method->is('disabled')) {
            return false;
        }
        $cb = explode('::', (string)$config->$method->callback);
        $cb[0] = Mage::getSingleton($cb[0]);
        if (empty($cb[0]) || empty($cb[1]) || !is_callable($cb)) {
            Mage::throwException(Mage::helper('udropship')->__('Invalid stock check callback: %s', (string)$config->$method->callback));
        }
        return $cb;
    }
    
    public function getStatementPoType()
    {
        $poType = $this->getData('statement_po_type');
        if ($poType == '999') {
            $poType = Mage::getStoreConfig('udropship/statement/statement_po_type');
        }
        return !empty($poType) && ($poType != 'po' || Mage::helper('udropship')->isUdpoActive()) ? $poType : 'shipment';
    }

    public function getStatementPoStatus()
    {
        $poStatus = $this->getData('statement_po_status');
        if (in_array('999', $poStatus) || empty($poStatus)) {
            if ($this->getStatementPoType()=='po' && Mage::helper('udropship')->isUdpoActive()) {
                $poStatus = Mage::getStoreConfig('udropship/statement/statement_po_status');
            } else {
                $poStatus = Mage::getStoreConfig('udropship/statement/statement_shipment_status');
            }
            if (!is_array($poStatus)) {
                $poStatus = explode(',', $poStatus);
            }
        }
        return $poStatus;
    }

    public function getStatementDiscountInPayout()
    {
        $ssInPayout = $this->getData('statement_discount_in_payout');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/statement_discount_in_payout');
        }
        return $ssInPayout;
    }

    public function getStatementTaxInPayout()
    {
        $ssInPayout = $this->getData('statement_tax_in_payout');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/statement_tax_in_payout');
        }
        return $ssInPayout;
    }

    public function getStatementShippingInPayout()
    {
        $ssInPayout = $this->getData('statement_shipping_in_payout');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/statement_shipping_in_payout');
        }
        return $ssInPayout;
    }

    public function getIsShippingTaxInShipping()
    {
        $ssInPayout = $this->getData('shipping_tax_in_shipping');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/shipping_tax_in_shipping');
        }
        return $ssInPayout;
    }

    public function getStatementSubtotalBase()
    {
        $ssInPayout = $this->getData('statement_subtotal_base');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/statement_subtotal_base');
        }
        return $ssInPayout;
    }

    public function getApplyCommissionOnTax()
    {
        $ssInPayout = $this->getData('apply_commission_on_tax');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/apply_commission_on_tax');
        }
        return $ssInPayout;
    }
    public function getApplyCommissionOnShipping()
    {
        $ssInPayout = $this->getData('apply_commission_on_shipping');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/apply_commission_on_shipping');
        }
        return $ssInPayout;
    }

    public function getApplyCommissionOnDiscount()
    {
        $ssInPayout = $this->getData('apply_commission_on_discount');
        if ('999' == $ssInPayout) {
            $ssInPayout = Mage::getStoreConfig('udropship/statement/apply_commission_on_discount');
        }
        return $ssInPayout;
    }

    public function getPayoutPoStatus()
    {
        return $this->getData('payout_po_status_type') == 'payout'
            ? $this->getData('payout_po_status')
            : $this->getStatementPoStatus();
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getData('status')) {
            $this->setData('status', 'I');
        }

        if ($this->hasData('url_key') && !$this->getData('url_key')) {
            $this->unsetData('url_key');
        } elseif ($this->getData('url_key')) {
            $data = $this->getData('url_key');
            $collection = $this->getCollection()->addFieldToFilter('url_key', $data);
            if ($this->getId()) { 
                $collection->addFieldToFilter('vendor_id', array('neq'=>$this->getId()));
            }
            if ($collection->count()) {
                Mage::throwException(Mage::helper('udropship')->__('This URL Key is already used for different vendor (%s). Please choose another.', htmlspecialchars($data)));
            }
            if (Mage::helper('udropship')->isUrlKeyReserved($data)) {
                Mage::throwException(Mage::helper('udropship')->__('This URL Key is reserved. Please choose another.'));
            }
        }

        //if ($this->getPassword()) {
            $collection = $this->getCollection()
                ->addFieldToFilter('vendor_id', array('neq'=>$this->getId()))
                ->addFieldToFilter('email', $this->getEmail());
            $dup = false;
            foreach ($collection as $dup) {
                if (Mage::getStoreConfig('udropship/vendor/unique_email')) {
                    Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied email already exists.'));
                }
                if (Mage::helper('core')->validateHash($this->getPassword(), $dup->getPasswordHash())) {
                    Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied email and password already exists.'));
                }
            }
            if (Mage::getStoreConfig('udropship/vendor/unique_vendor_name')) {
                $collection = $this->getCollection()
                    ->addFieldToFilter('vendor_id', array('neq'=>$this->getId()))
                    ->addFieldToFilter('vendor_name', $this->getVendorName());
                $dup = false;
                foreach ($collection as $dup) {
                    Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied name already exists.'));
                }
            }
        //}

        $handlingConfig = $this->getData('handling_config');
        if (is_array($handlingConfig) && !empty($handlingConfig)
            && !empty($handlingConfig['limit']) && is_array($handlingConfig['limit'])
        ) {
            reset($handlingConfig['limit']);
            $firstTitleKey = key($handlingConfig['limit']);
            if (!is_numeric($firstTitleKey)) {
                $newHandlingConfig = array();
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($handlingConfig['limit'] as $_k => $_t) {
                    if ( ($_limit = $filter->filter($handlingConfig['limit'][$_k]))
                        && false !== ($_value = $filter->filter($handlingConfig['value'][$_k]))
                    ) {
                        $_limit = is_numeric($_limit) ? $_limit : '*';
                        $_sk    = is_numeric($_limit) ? $_limit : '9999999999';
                        $_sk    = 'str'.str_pad((string)$_sk, 20, '1', STR_PAD_LEFT);
                        $newHandlingConfig[$_sk] = array(
                            'limit' => $_limit,
                            'value' => $_value,
                        );
                    }
                }
                ksort($newHandlingConfig);
                $newHandlingConfig = array_values($newHandlingConfig);
                $this->setData('handling_config', array_values($newHandlingConfig));
            }
        }

        $callEndiciaChangePass = true;
        foreach (array('endicia_requester_id', 'endicia_account_id', 'endicia_pass_phrase') as $eKey) {
            if (!$this->getData($eKey)) {
                $callEndiciaChangePass = false;
                break;
            }
        }
        $eNewPh = $this->getData('endicia_new_pass_phrase');
        $eNewPhC = $this->getData('endicia_new_pass_phrase_confirm');
        $callEndiciaChangePass = $callEndiciaChangePass && $eNewPh;
        if ($callEndiciaChangePass) {
            if ((string)$eNewPh!=(string)$eNewPhC) {
                Mage::throwException('"Endicia New Pass Phrase" should match "Endicia Confirm New Pass Phrase"');
            }
            Mage::helper('udropship')->getLabelCarrierInstance('usps')->setVendor($this)->changePassPhrase($eNewPh);
            $this->setData('endicia_pass_phrase', $eNewPh);
            $this->unsetData('endicia_new_pass_phrase');
            $this->unsetData('endicia_new_pass_phrase_confirm');
        }

        Mage::helper('udropship')->processCustomVars($this);
    }
    
    public function getHidePackingslipAmount()
    {
        if ($this->getData('hide_packingslip_amount')==-1) {
            return Mage::getStoreConfigFlag('udropship/vendor/hide_packingslip_amount');
        } else {
            return $this->getData('hide_packingslip_amount');
        }
    }

    public function getHideUdpoPdfShippingAmount()
    {
        if ($this->getData('hide_udpo_pdf_shipping_amount')==-1) {
            return Mage::getStoreConfigFlag('udropship/vendor/hide_udpo_pdf_shipping_amount');
        } else {
            return $this->getData('hide_udpo_pdf_shipping_amount');
        }
    }

    public function getShowManualUdpoPdfShippingAmount()
    {
        if ($this->getData('show_manual_udpo_pdf_shipping_amount')==-1) {
            return Mage::getStoreConfigFlag('udropship/vendor/show_manual_udpo_pdf_shipping_amount');
        } else {
            return $this->getData('show_manual_udpo_pdf_shipping_amount');
        }
    }

    public function hasImageUpload($flag=null)
    {
        $oldFlag = $this->_hasImageUpload;
        if ($flag!==null) {
            $this->_hasImageUpload = $flag;
        }
        return $oldFlag;
    }
    protected $_hasImageUpload=false;
    protected function _afterSave()
    {
        parent::_afterSave();

        if (!empty($_FILES)) {
            $baseDir = Mage::getConfig()->getBaseDir('media').DS.'vendor'.DS.$this->getId();
            Mage::getConfig()->createDirIfNotExists($baseDir);
            $changedFields = array();
            foreach ($_FILES as $k=>$img) {
                if (empty($img['tmp_name']) || empty($img['name']) || empty($img['type'])) {
                    continue;
                }
                if (!@move_uploaded_file($img['tmp_name'], $baseDir.DS.$img['name'])) {
                    Mage::throwException('Error while uploading file: '.$img['name']);
                }
                $changedFields[] = $k;
                $this->setData($k, 'vendor/'.$this->getId().'/'.$img['name']);
            }
            if (!empty($changedFields)) {
                $this->_hasImageUpload = true;
                $changedFields[] = 'custom_vars_combined';
                Mage::helper('udropship')->processCustomVars($this);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($this, $changedFields);
            }
        }
    }

    public function afterCommitCallback()
    {
        if (!$this->getSkipUdropshipVendorIndexer()) {
            Mage::getSingleton('index/indexer')->processEntityAction(
                $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
            );
        }
        parent::afterCommitCallback();
        $this->_hasImageUpload = false;
        return $this;
    }

    public function isCountryMatch($countryId)
    {
        if (trim($countryId)=='') return true;
        $match = true;
        $allowed = $this->getAllowedCountries();
        if (!empty($allowed) && !in_array('*', $allowed) && !in_array($countryId, $allowed)) {
            $match = false;
        }
        return $match;
    }
    public function isZipcodeMatch($zipCode)
    {
    	return Mage::helper('udropship')->isZipcodeMatch($zipCode, $this->getLimitZipcode());
    }
    public function isAddressMatch($address)
    {
        $result = true;
        static $transport;
        if ($transport === null) {
            $transport = new Varien_Object;
        }
        $transport->setAllowed($result);
        if ($address) {
            Mage::dispatchEvent('udropship_vendor_is_address_match', array('address' => $address, 'vendor' => $this, 'transport' => $transport));
        }
        return $transport->getAllowed();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        Mage::helper('udropship')->loadFilteredCustomData($this, $this->getDirectFields());
        Mage::helper('udropship')->getVendor($this);
        $this->unsetData('endicia_new_pass_phrase');
        $this->unsetData('endicia_new_pass_phrase_confirm');
    }

    public function getDirectFields()
    {
        return $this->_getResource()->getDirectFields();
    }

    public function afterLoad()
    {
        parent::afterLoad();
        return $this; // added for chaining
    }

    public function updateData($data)
    {
        $this->addData($data);
        $this->getResource()->updateData($this, $data);
        return $this;
    }

    public function getHandlingFee()
    {
        $handlingConfig = $this->getData('handling_config');
        if (is_array($handlingConfig) && !empty($handlingConfig)
            && ($request = $this->getData('__carrier_rate_request'))
            && $request instanceof Mage_Shipping_Model_Rate_Request
            && $this->getData('use_handling_fee') == Unirgy_Dropship_Model_Source::HANDLING_ADVANCED
        ) {
            $ruleValue = null;
            switch ($this->getData('handling_rule')) {
                case 'price':
                    $ruleValue = $request->getData('package_value');
                    break;
                case 'cost':
                    $ruleValue = $request->getData('package_cost');
                    break;
                case 'qty':
                    $ruleValue = $request->getData('package_qty');
                    break;
                case 'line':
                    $ruleValue = $request->getData('package_lines');
                    break;
                case 'weight':
                    $ruleValue = $request->getData('package_weight');
                    break;
            }
            if (!is_null($ruleValue)) {
                foreach ($handlingConfig as $hc) {
                    if (!isset($hc['limit']) || !isset($hc['value'])) continue;
                    if (is_numeric($hc['limit']) && $ruleValue<=$hc['limit']
                        || !is_numeric($hc['limit'])
                    ) {
                        $handlingFee = $hc['value'];
                        break;
                    }
                }
                if (isset($handlingFee)) {
                    return $handlingFee;
                }
            }
        }
        return $this->getData('handling_fee');
    }

    public function getBackorderByAvailability()
    {
        return Mage::getStoreConfig('udropship/stock/backorder_by_availability')
            && $this->getData('backorder_by_availability');
    }
    public function getUseReservedQty()
    {
        return Mage::getStoreConfig('udropship/stock/use_reserved_qty')
            && $this->getData('use_reserved_qty');
    }
    public function getAllowShippingExtraCharge()
    {
        return Mage::getStoreConfig('udropship/customer/allow_shipping_extra_charge')
            && $this->getData('allow_shipping_extra_charge');
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    public function getShowProductsMenuItem()
    {
        $show = Mage::getStoreConfigFlag('udropship/microsite/show_products_menu_item');
        if (-1!=$this->getData('show_products_menu_item')) {
            $show = $this->getData('show_products_menu_item');
        }
        if (Mage::helper('udropship')->isEE()) {
            $show = 0;
        }
        return $show;
    }

    public function getVendorLandingPage()
    {
        if (!Mage::helper('udropship')->isModuleActive('Unirgy_DropshipMicrositePro')) return false;
        $pageId = Mage::getStoreConfig('web/default/umicrosite_default_landingpage');
        if (-1!=$this->getData('cms_landing_page') && $this->getData('cms_landing_page')) {
            $pageId = $this->getData('cms_landing_page');
        }
        return $pageId;
    }

    public function getAllowTiershipModify()
    {
        return Mage::helper('udropship')->isModuleActive('udtiership') && Mage::getStoreConfig('carriers/udtiership/allow_vendor_modify');
    }

}
