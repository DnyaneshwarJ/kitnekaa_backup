<?php
	$installer = $this;
	$installer->startSetup();
	$installer->getConnection()
	->modifyColumn($installer->getTable('users/company'),'vat_tin_verified',array(
		'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'nullable' => true
	));
	$installer->endSetup();
?>