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

class Unirgy_Dropship_Model_Vendor_NotifyLowstock extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_notifyLowstock');
        parent::_construct();
    }

    public function vendorNotifyLowstock()
    {
        $vendors = Mage::getResourceModel('udropship/vendor_collection')->addFieldToFilter('notify_lowstock',1);
        $hasEmail = false;
        foreach ($vendors as $vendor) {
            $lsCollection = Mage::getResourceModel('udropship/vendor_notifyLowstock_collection')->initLowstockSelect($vendor);
            if ($lsCollection->count()>0) {
                $vsAttr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute');
                if (!Mage::helper('udropship')->isUdmultiAvailable()) {
                    if ($vsAttr && $vsAttr!='sku' && Mage::helper('udropship')->checkProductAttribute($vsAttr)) {
                        foreach ($lsCollection as $prod) {
                            $prod->setVendorSku($prod->getData($vsAttr));
                        }
                    }
                }
                $this->sendLowstockNotificationEmail($lsCollection, $vendor);
                $lsCollection->markLowstockNotified();
                $hasEmail = true;
            }
        }
        if ($hasEmail) Mage::helper('udropship')->processQueue();
        return $this;
    }
    public function vendorCleanLowstock()
    {
        $this->getResource()->vendorCleanLowstock();
        return $this;
    }
    
    public function sendLowstockNotificationEmail($lsCollection, $vendor)
    {
        $hlp = Mage::helper('udropship');
        $store = Mage::app()->getStore();
        $hlp->setDesignStore($store);

        $data = array(
            'vendor'      => $vendor,
            'store_name'  => $store->getName(),
            'vendor_name' => $vendor->getVendorName(),
            'stock_url'   => Mage::getUrl('udropship/vendor/product'),
        );
        
        $data['notification_grid'] = Mage::helper('productalert')->createBlock('core/template')
            ->setTemplate('unirgy/dropship/email/vendor/notification/stockItems.phtml')
            ->setStockItems($lsCollection)
            ->toHtml();

        $template = $store->getConfig('udropship/vendor/notify_lowstock_email_template');
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }
        Mage::getModel('udropship/email')->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);

        $hlp->setDesignStore();
    }
}