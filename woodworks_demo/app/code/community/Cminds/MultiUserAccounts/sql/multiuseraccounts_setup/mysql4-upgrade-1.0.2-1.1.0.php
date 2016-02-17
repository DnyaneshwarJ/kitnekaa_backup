<?php
$installer=$this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales/order'),
        'subaccount_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'default' => null,
            'comment' => 'Subaccount Id'
        )
    );
$installer->endSetup();
