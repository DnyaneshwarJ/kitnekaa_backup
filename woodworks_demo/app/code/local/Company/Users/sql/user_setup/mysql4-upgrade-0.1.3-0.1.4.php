<?php
	$installer = $this;
	$installer->startSetup();
	$installer->getConnection()
	->addColumn($installer->getTable('users/company'),'vat_tin_verified',array(
		'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'nullable' => false,
		'default' => 0,
		'comment' => 'Vat Tin Verified'
	));
	$installer->endSetup();
?>