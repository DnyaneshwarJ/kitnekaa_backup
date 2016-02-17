<?php

/**
 * @author CreativeMindsSolutions
 */
class Cminds_MultiUserAccounts_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    protected function _beforeToHtml()
    {
        $this->_addManageUserNavigation();
        return $this;
    }

    public function removeLink($name)
    {
        unset($this->_links[$name]);
        return $this;
    }

    public function removeLinkByName($name)
    {
        unset($this->_links[$name]);
        return $this;
    }

    protected function _addManageUserNavigation()
    {
        $helper = Mage::helper('cminds_multiuseraccounts');
        if ($helper->isEnabled() && !$helper->isSubAccountMode()) {
            $this->addLink('sub_account', 'customer/account/subAccount', $this->__('Manage Users'));
        }
    }

    public function addLink($name, $path, $label, $urlParams=array()) {
        if(Mage::getConfig()->getModuleConfig('Cminds_Marketplace')->is('active', 'true')){
            $configLabelName = Mage::getStoreConfig('supplierfrontendproductuploader_catalog/supplierfrontendproductuploader_presentation/account_page_label');

            if($name == 'supplierfrontendproductuploader') {
                if(!Mage::helper('supplierfrontendproductuploader')->hasAccess() || !Mage::helper('supplierfrontendproductuploader')->isEnabled()) {
                    return $this;
                }

                if($configLabelName != '') {
                    $label = $configLabelName;
                }
            }

            if($name == 'marketplace_supplier_rate' || $name == 'marketplace_supplier_rates') {
                if(!Mage::helper('supplierfrontendproductuploader')->isEnabled()) {
                    return $this;
                }

                if(!Mage::getStoreConfig('marketplace_configuration/presentation/supplier_rating')) {
                    return $this;
                }
            }
        }

        $this->_links[$name] = new Varien_Object(array(
            'name' => $name,
            'path' => $path,
            'label' => $label,
            'url' => $this->getUrl($path, $urlParams),
        ));
        
        return $this;
    }
}