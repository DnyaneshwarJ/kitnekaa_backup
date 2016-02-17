<?php

class Cminds_MultiUserAccounts_Block_Adminhtml_Customer_Edit_Tab_SubAccount
    extends Cminds_MultiUserAccounts_Block_Adminhtml_SubAccount_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_edit_tab_subaccount');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Sub Accounts');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Sub Accounts');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/subAccount/subAccountGrid', array('_current'=>true));
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'addresses';
    }

    /**
     * Prepare collection for grid
     *
     * @return Cminds_MultiUserAccounts_Block_Adminhtml_Customer_Edit_Tab_SubAccount
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('cminds_multiuseraccounts/subAccount_collection')
            ->addFieldToFilter('parent_customer_id', Mage::registry('current_customer')->getId());
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}
