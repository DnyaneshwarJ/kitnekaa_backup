<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 26.02.13
 * Time: 22:42
 *
 */

class Unirgy_SimpleLicense_Model_Resource_Setup
    extends Mage_Core_Model_Resource_Setup
{
    public function reinstall()
    {
        $configVer = (string)$this->_moduleConfig->version;

        $this->_installResourceDb($configVer);
    }
}