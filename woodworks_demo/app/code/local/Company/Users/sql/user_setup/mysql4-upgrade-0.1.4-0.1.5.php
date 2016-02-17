<?php
	$installer = $this;
	$installer->startSetup();
	$installer->getConnection()
	->addColumn($installer->getTable('users/company'),'vat_tin_verified_by',array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'nullable' => true,
		'length' => 255,
		'comment' => 'Vat Tin Verified By'
	));
	$installer->endSetup();
?>