<?php
class Bobcares_Quote2Sales_Block_Adminhtml_Quote_View_Info extends Bobcares_Quote2Sales_Block_Adminhtml_Quote_Abstract
{
    /**
     * Retrieve required options from parent
     */
    protected function _beforeToHtml()
    {
    	parent::_beforeToHtml();
		$this->setOrder($this->getQuote());
        foreach ($this->getQuoteInfoData() as $k => $v) {
            $this->setDataUsingMethod($k, $v);
        }
    }

    public function getOrderStoreName()
    {
        if ($this->getQuote()) {
            $storeId = $this->getQuote()->getStoreId();
            if (is_null($storeId)) {
                $deleted = Mage::helper('adminhtml')->__(' [deleted]');
                return nl2br($this->getQuote()->getStoreName()) . $deleted;
            }
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }

    public function getCustomerGroupName()
    {
        if ($this->getQuote()) {
            return Mage::getModel('customer/group')->load((int)$this->getQuote()->getCustomerGroupId())->getCode();
        }
        return null;
    }

	 public function getCustomerName()
    {
        if ($this->getQuote()->getCustomerFirstname()) {
            $customerName = $this->getQuote()->getCustomerFirstname() . ' ' . $this->getQuote()->getCustomerLastname();
        }
        else {
            $customerName = Mage::helper('quote2sales')->__('Guest');
        }
        return $customerName;
    }
    public function getCustomerViewUrl()
    { return false; 
        if ($this->getQuote()->getCustomerIsGuest() || !$this->getQuote()->getCustomerId()) {
            return false;
        }
        return $this->getUrl('*/customer/edit', array('id' => $this->getQuote()->getCustomerId()));
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('*/adminhtml_quote/view', array('quote_id'=>$orderId));
    }

    /**
     * Find sort order for account data
     * Sort Order used as array key
     *
     * @param array $data
     * @param int $sortOrder
     * @return int
     */
    protected function _prepareAccountDataSortOrder(array $data, $sortOrder)
    {
        if (isset($data[$sortOrder])) {
            return $this->_prepareAccountDataSortOrder($data, $sortOrder + 1);
        }
        return $sortOrder;
    }

    /**
     * Return array of additional account data
     * Value is option style array
     *
     * @return array
     */
    public function getCustomerAccountData()
    {
        $accountData = array();

        /* @var $config Mage_Eav_Model_Config */
        $config     = Mage::getSingleton('eav/config');
        $entityType = 'customer';
        $customer   = Mage::getModel('customer/customer');
        foreach ($config->getEntityAttributeCodes($entityType) as $attributeCode) {
            /* @var $attribute Mage_Customer_Model_Attribute */
            $attribute = $config->getAttribute($entityType, $attributeCode);
            if (!$attribute->getIsVisible() || $attribute->getIsSystem()) {
                continue;
            }
            $orderKey   = sprintf('customer_%s', $attribute->getAttributeCode());
            $orderValue = $this->getQuote()->getData($orderKey);
            if ($orderValue != '') {
                $customer->setData($attribute->getAttributeCode(), $orderValue);
                $dataModel  = Mage_Customer_Model_Attribute_Data::factory($attribute, $customer);
                $value      = $dataModel->outputValue(Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_HTML);
                $sortOrder  = $attribute->getSortOrder() + $attribute->getIsUserDefined() ? 200 : 0;
                $sortOrder  = $this->_prepareAccountDataSortOrder($accountData, $sortOrder);
                $accountData[$sortOrder] = array(
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->escapeHtml($value, array('br'))
                );
            }
        }

        ksort($accountData, SORT_NUMERIC);

        return $accountData;
    }

    /**
     * Get link to edit order address page
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @param string $label
     * @return string
     */
    public function getAddressEditLink($address, $label='')
    {
        if (empty($label)) {
            $label = $this->__('Edit');
        }
        $url = $this->getUrl('*/sales_order/address', array('address_id'=>$address->getId()));
        return '<a href="'.$url.'">' . $label . '</a>';
    }

    /**
     * Whether Customer IP address should be displayed on sales documents
     * @return bool
     */
    public function shouldDisplayCustomerIp()
    {
        return !Mage::getStoreConfigFlag('sales/general/hide_customer_ip', $this->getOrder()->getStoreId());
    }
}
