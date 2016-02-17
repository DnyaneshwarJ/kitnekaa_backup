<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 09:29
 */
$installer = $this;

/*
 * @var $installer Sm_Cameraslide_Model_Resource_Setup
 * */
$installer->startSetup();

/*
 * create table 'sm_camera_slide'
 * */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sm_cameraslide/slide'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Slide Id')
    ->addColumn('name_slide', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Name Slide')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Status')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'Params')
    ->setComment('Sm Camera Slide in Backend');
$installer->getConnection()->createTable($table);

/*
 * create table 'sm_camera_sliders'
 * */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sm_cameraslide/sliders'))
    ->addColumn('sliders_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Sliders Id')
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false
    ), 'Slide Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Status')
    ->addColumn('priority', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Priority')
    ->addColumn('params', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'Status')
    ->addColumn('layers', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'Layers')
    ->setComment('Sm Camera Sliders in Backend');
$installer->getConnection()->createTable($table);
$installer->endSetup();