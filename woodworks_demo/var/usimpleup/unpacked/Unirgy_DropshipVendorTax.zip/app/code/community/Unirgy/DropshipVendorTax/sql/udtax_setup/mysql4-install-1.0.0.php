<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($this->getTable('udropship/vendor'), 'vendor_tax_class', 'varchar(255)');
$conn->addColumn($this->getTable('tax_calculation'), 'vendor_tax_class_id', 'smallint(6) NOT NULL');

$constraints = array(
    'tax_calculation' => array(
        'vendor_tax_class' => array('vendor_tax_class_id', 'tax_class', 'class_id'),
    ),
);

foreach ($constraints as $table => $list) {
    foreach ($list as $code => $constraint) {
        $constraint[1] = $installer->getTable($constraint[1]);
        array_unshift($constraint, $installer->getTable($table));
        array_unshift($constraint, strtoupper($table . '_' . $code));

        call_user_func_array(array($installer->getConnection(), 'addConstraint'), $constraint);
    }
}

$installer->endSetup();
