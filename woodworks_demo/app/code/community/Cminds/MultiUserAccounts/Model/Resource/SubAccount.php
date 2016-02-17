<?php

class Cminds_MultiUserAccounts_Model_Resource_SubAccount extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('cminds_multiuseraccounts/subAccount', 'entity_id');
    }

    /**
     * TODO: CONFIRMATION
     *
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $subAccount)
    {
        parent::_beforeSave($subAccount);

        if (!$subAccount->getEmail()) {
            throw Mage::exception('Mage_Customer', Mage::helper('customer')->__('Customer email is required'));
        }

        if ($this->_checkUniqueEmail($subAccount)) {
            throw Mage::exception(
                'Mage_Customer', Mage::helper('cminds_multiuseraccounts')->__('This user email already exists'),
                Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS
            );
        }

//         set confirmation key logic
        if ($subAccount->getForceConfirmation()) {
            $subAccount->setConfirmation(1);
        } elseif (!$subAccount->getId() && $subAccount->isConfirmationRequired()) {
            $subAccount->setConfirmation($subAccount->getRandomConfirmationKey());
        }
        // remove customer confirmation key from database, if empty
        if (!$subAccount->getConfirmation()) {
            $subAccount->setConfirmation(null);
        }

        return $this;
    }

    protected function _checkUniqueEmail($subAccount)
    {
        $accountResource = Mage::getModel('customer/customer')->getResource();
        $customerCheck = $this->_getCheckUniqueEmailResult($accountResource->getEntityTable(), $accountResource->getEntityIdField(), $subAccount);
        $subAccountCheck = $this->_getCheckUniqueEmailResult($this->getMainTable(), $this->getIdFieldName(), $subAccount, $subAccount->getId());

        return ($customerCheck || $subAccountCheck);

    }

    protected function _getCheckUniqueEmailResult($table, $idField, $subAccount, $idCheck = null)
    {
        $adapter = $this->_getWriteAdapter();
        $bind = array('email' => $subAccount->getEmail());

        $select = $adapter->select()
            ->from($table, array($idField))
            ->where('email = :email');

        if ($subAccount->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$subAccount->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        if ($idCheck) {
            $bind['entity_id'] = (int)$idCheck;
            $select->where('entity_id != :entity_id');
        }

        $result = $adapter->fetchOne($select, $bind);

        return $result;
    }

    public function loadByEmail(Cminds_MultiUserAccounts_Model_SubAccount $subAccount, $email, $testOnly = false)
    {
        $adapter = $this->_getReadAdapter();
        $bind = array('customer_email' => $email);
        $select = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName()))
            ->where('email = :customer_email');

        if ($subAccount->getSharingConfig()->isWebsiteScope()) {
            if (!$subAccount->hasData('website_id')) {
                Mage::throwException(
                    Mage::helper('customer')->__('Sub Account website ID must be specified when using the website scope')
                );
            }
            $bind['website_id'] = (int)$subAccount->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        $subAccountId = $adapter->fetchOne($select, $bind);
        if ($subAccountId) {
            $this->load($subAccount, $subAccountId);
        } else {
            $subAccount->setData(array());
        }
        return $this;
    }
}
