<?php

$this->startSetup();

$this->run("
update `{$this->getTable('core/config_data')}` set `value`=case `value` when 0 then 'local_if_in_stock' when 1 then 'always_assigned' end where `path`='udropship/stock/availability'
");

$this->endSetup();