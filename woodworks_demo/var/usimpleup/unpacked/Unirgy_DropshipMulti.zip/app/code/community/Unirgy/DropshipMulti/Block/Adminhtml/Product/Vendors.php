<?php

class Unirgy_DropshipMulti_Block_Adminhtml_Product_Vendors
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize block
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setProductId($this->getRequest()->getParam('id'));
        $this->setTemplate('udmulti/product/vendors.phtml');
        $this->setId('udmulti_vendors');
        $this->setUseAjax(true);
    }

    public function getAssociatedVendors()
    {
        $assocVendor = Mage::getModel('udropship/vendor_product')->getCollection()
            ->addProductFilter($this->getProduct()->getId());
        $assocVendor->getSelect()->join(array('uv' => $assocVendor->getTable('udropship/vendor')), 'uv.vendor_id=main_table.vendor_id', array('vendor_name'));
        $gpData = Mage::helper('udmulti')->getMvGroupPrice(array($this->getProduct()->getId()));
        $tpData = Mage::helper('udmulti')->getMvTierPrice(array($this->getProduct()->getId()));
        foreach ($assocVendor as $vp) {
            $udmTierPrice = $udmGroupPrice = array();
            foreach ($gpData as $__gpd) {
                if ($vp->getProductId() != $__gpd->getProductId() || $vp->getVendorId() != $__gpd->getVendorId()) continue;
                if ($__gpd->getData('all_groups')) {
                    $__gpd->setData('customer_group_id', Mage_Customer_Model_Group::CUST_GROUP_ALL);
                }
                $udmGroupPrice[] = $__gpd->getData();
            }
            foreach ($tpData as $__tpd) {
                if ($vp->getProductId() != $__tpd->getProductId() || $vp->getVendorId() != $__tpd->getVendorId()) continue;
                if ($__tpd->getData('all_groups')) {
                    $__tpd->setData('customer_group_id', Mage_Customer_Model_Group::CUST_GROUP_ALL);
                }
                $udmTierPrice[] = $__tpd->getData();
            }
            $vp->setData('group_price', $udmGroupPrice);
            $vp->setData('tier_price', $udmTierPrice);
        }
        return $assocVendor;
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
         return $this->_getProduct()->getCompositeReadonly();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Escape JavaScript string
     *
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        return addcslashes($string, "'\r\n\\");
    }

    /**
     * Retrieve Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('udropship')->__('Drop Shipping Vendors');
    }

    /**
     * Retrieve Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('udropship')->__('Drop Shipping Vendors');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    
    public function getVendorName($vId)
    {
        $v = Mage::helper('udropship')->getVendor($vId);
        return $v && $v->getId() ? $v->getVendorName() : '';
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : 'udmulti_vendors');
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

    public function getGroupPriceBlock($fieldName)
    {
        return Mage::app()->getLayout()->getBlockSingleton('udmulti/adminhtml_product_groupPrice')
            ->setTemplate('udmulti/product/group_price.phtml')
            ->setFieldName($fieldName)
            ->setParentBlock($this);
    }
    public function getTierPriceBlock($fieldName)
    {
        return Mage::app()->getLayout()->getBlockSingleton('udmulti/adminhtml_product_groupPrice')
            ->setTemplate('udmulti/product/tier_price.phtml')
            ->setFieldName($fieldName)
            ->setParentBlock($this);
    }
}
