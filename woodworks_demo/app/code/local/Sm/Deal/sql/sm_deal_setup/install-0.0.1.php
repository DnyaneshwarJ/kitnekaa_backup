<?php 
$this->startSetup();
$table = $this->getConnection()
	->newTable($this->getTable('deal/deal'))
	->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Deal ID')
	->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false,
		), 'Deal Name')

	->addColumn('start_date', Varien_Db_Ddl_Table::TYPE_DATETIME, 255, array(
		'nullable'  => false,
		), 'Start Date')

	->addColumn('end_date', Varien_Db_Ddl_Table::TYPE_DATETIME, 255, array(
		'nullable'  => false,
		), 'End Date')

	->addColumn('url_key', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'URL key')

	->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Status')

	->addColumn('in_rss', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'In RSS')

	->addColumn('meta_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'Meta title')

	->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
		), 'Meta keywords')

	->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
		), 'Meta description')

	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		), 'Deal Creation Time')
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		), 'Deal Modification Time')
	->setComment('Deal Table');
$this->getConnection()->createTable($table);

$table = $this->getConnection()
	->newTable($this->getTable('deal/deal_store'))
	->addColumn('deal_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false,
		'primary'   => true,
		), 'Deal ID')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Store ID')
	->addIndex($this->getIdxName('deal/deal_store', array('store_id')), array('store_id'))
	->addForeignKey($this->getFkName('deal/deal_store', 'deal_id', 'deal/deal', 'entity_id'), 'deal_id', $this->getTable('deal/deal'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($this->getFkName('deal/deal_store', 'store_id', 'core/store', 'store_id'), 'store_id', $this->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Deal To Store Linkage Table');
$this->getConnection()->createTable($table);
$table = $this->getConnection()
	->newTable($this->getTable('deal/deal_product'))
	->addColumn('rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Category ID')
	->addColumn('deal_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
	), 'Deal ID')
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
	), 'Product ID')
	->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
		'default'   => '0',
	), 'Position')
	->addIndex($this->getIdxName('deal/deal_product', array('product_id')), array('product_id'))
	->addForeignKey($this->getFkName('deal/deal_product', 'deal_id', 'deal/deal', 'entity_id'), 'deal_id', $this->getTable('deal/deal'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($this->getFkName('deal/deal_product', 'product_id', 'catalog/product', 'entity_id'),	'product_id', $this->getTable('catalog/product'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Deal to Product Linkage Table');
$this->getConnection()->createTable($table);
$this->endSetup();