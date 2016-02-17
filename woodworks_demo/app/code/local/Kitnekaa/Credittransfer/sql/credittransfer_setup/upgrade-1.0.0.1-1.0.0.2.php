<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table docs_needed(company_id int not null,doc_id int not null);
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 