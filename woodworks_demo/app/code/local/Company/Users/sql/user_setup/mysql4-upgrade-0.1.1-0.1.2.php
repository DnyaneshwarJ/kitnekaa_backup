<?php
	$installer = $this;
	$installer->startSetup();
	$installer->getConnection()
	->addColumn($installer->getTable('users/company'),'vat_no',array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'nullable' => true,
		'length' => 255,
		'comment' => 'Vat Number'
	));
	$installer->endSetup();
?>