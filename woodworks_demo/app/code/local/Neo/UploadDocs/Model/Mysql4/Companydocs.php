<?php
class Neo_UploadDocs_Model_Mysql4_Companydocs extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("uploaddocs/companydocs", "id");
    }
}