<?php

class UnirgyCustom_DropshipQuote2sale_Helper_Data extends Mage_Core_Helper_Abstract
{   /* Get Current vendor id of logged in user */
    public function getVendorId()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        return (!is_null($admin->getVendorId())) ? $admin->getVendorId() : Mage::getStoreConfig('udropship/vendor/local_vendor');
    }

    /* Check logged in user is seller or not*/
    public function isSeller()
    {
        $admin = Mage::getSingleton('admin/session')->getUser();
        return (!is_null($admin->getVendorId())) ? true : false;
    }

    /**
     * Get Product Price for vendor
     *
     * @param   Product_Id , Vendor_Id
     * @return  float
     */
    public function getVendorPriceByIds($_product_id, $_vendor_id)
    {
        $product = Mage::getModel('catalog/product')->load($_product_id);
        $product_vendors = $product->getMultiVendorData();
        $price = 0;
        foreach ($product_vendors as $v) {
            if ($v['vendor_id'] == $_vendor_id) {
                $price = $v['vendor_price'] ? $v['vendor_price'] : $product->getFinalPrice();
                break;
            }
        }
        return $price;
    }

    /* Get vendors details by quote id*/
    public function getAllQuotesVendors()
    {
        $quote_vendors = null;
        $collection = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter("grand_total", array("gt" => 0))
            ->setOrder('created_at', 'desc')
            ->addFieldToFilter("grand_total", array("gt" => 0));
        $collection->getSelect()->join('udropship_vendor', 'main_table.vendor_id=udropship_vendor.vendor_id', array('vendor_id', 'vendor_name'));

        foreach ($collection as $obj) {
            $quote_vendors[$obj->getId()] = new Varien_Object(array('vendor_id' => $obj->getVendorId(), 'vendor_name' => $obj->getVendorName()));
        }

        return $quote_vendors;
    }
}
	 