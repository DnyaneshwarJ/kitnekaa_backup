<?php
	$installer = $this;
	$installer->startSetup();
	$installer->getConnection()
	->addColumn($installer->getTable('users/company'),'company_type',array(
		'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'nullable' => true,
		'comment' => 'Company Type'
	));
	$installer->endSetup();
?>