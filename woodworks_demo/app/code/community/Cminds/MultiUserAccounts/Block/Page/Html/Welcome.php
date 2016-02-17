<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Block_Page_Html_Welcome extends Mage_Page_Block_Html_Welcome
{
    protected function _toHtml()
    {
        $name = Mage::getSingleton('customer/session')->getCustomer()->getName();
        $helper = Mage::helper('cminds_multiuseraccounts');

        if ($subAccount = $helper->isSubAccountMode()) {
            $name = $subAccount->getName();
        }

        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->escapeHtml($name));
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }
}