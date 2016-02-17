<?php

class Company_CustomMultiUserAccounts_Model_SubAccount_Permission extends Cminds_MultiUserAccounts_Model_SubAccount_Permission
{
    const PERMISSION_READ = 1;
    const PERMISSION_WRITE = 2;
    const PERMISSION_ORDER = 3;
    const PERMISSION_ORDER_WRITE = 4;
    const PERMISSION_ADMIN = 5;

    /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::PERMISSION_ADMIN => Mage::helper('cminds_multiuseraccounts')->__('Admin'),
            self::PERMISSION_READ => Mage::helper('cminds_multiuseraccounts')->__('Read Only'),
            //self::PERMISSION_WRITE => Mage::helper('cminds_multiuseraccounts')->__('Modify Account'),
            self::PERMISSION_ORDER => Mage::helper('cminds_multiuseraccounts')->__('Purchase Order'),
            //self::PERMISSION_ORDER_WRITE => Mage::helper('cminds_multiuseraccounts')->__('Order Creation & Modify Account'),
        );
    }

    static public function getWritePermission()
    {
        return array(
            self::PERMISSION_WRITE,
            self::PERMISSION_ORDER_WRITE,
        );
    }

    static public function getOrderCreationPermission()
    {
        return array(
            self::PERMISSION_ORDER,
            self::PERMISSION_ORDER_WRITE,
        );
    }
    static public function getAdminPermission()
    {
        return self::PERMISSION_ADMIN;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value' => '', 'label' => ''));
        return $options;
    }

    /**
     * Retireve all options
     *
     * @return array
     */
    static public function getAllOptions()
    {
        $res = array();
        $res[] = array('value' => '', 'label' => Mage::helper('cminds_multiuseraccounts')->__('-- Please Select --'));
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
                'value' => $index,
                'label' => $value
            );
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     */
    static public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}

