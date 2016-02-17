<?php
$installer=$this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('cminds_multiuseraccounts/subAccount'),
        'view_all_orders',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Permission to view all customer orders'
        )
    );
$installer->endSetup();
