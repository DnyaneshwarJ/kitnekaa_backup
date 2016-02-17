<?php

class Cminds_MultiUserAccounts_Block_SubAccount_Edit extends Cminds_MultiUserAccounts_Block_SubAccount_Abstract
{
    public function getInfoBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock('cminds_multiuseraccounts/subAccount_widget_info')
            ->setObject($this->getSubAccount());

        return $nameBlock->toHtml();
    }

    public function getPostActionUrl()
    {
        return $this->getUrl('customer/account/editsubaccountpost');
    }
}
