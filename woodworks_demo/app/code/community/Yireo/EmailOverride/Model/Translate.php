<?php
/**
 * Yireo EmailOverride for Magento 
 *
 * @package     Yireo_EmailOverride
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

/**
 * EmailOverride Core model
 */
class Yireo_EmailOverride_Model_Translate extends Mage_Core_Model_Translate
{
    /**
     * Retrieve translation file for module
     *
     * @param string $module
     * @param string $fileName
     *
     * @return string
     */
    protected function _getModuleFilePath($module, $fileName)
    {
        if (Mage::app()->getStore()->isAdmin() == true) {
            return parent::_getModuleFilePath($module, $fileName);
        }

        $localeCode = $this->getLocale();
        if (empty($localeCode)) {
            return parent::_getModuleFilePath($module, $fileName);
        }

        $filePath = $this->getLocaleOverrideFile($localeCode, $fileName);
        if (!empty($filePath) && file_exists($filePath)) {
            return $filePath;
        }

        return parent::_getModuleFilePath($module, $fileName);
    }

    /**
     * Retrieve translated template file
     * Try current design package first
     *
     * @param string $file
     * @param string $type
     * @param string $localeCode
     * @return string
     */
    public function getTemplateFile($file, $type, $localeCode=null)
    {
        if (is_null($localeCode) || preg_match('/[^a-zA-Z_]/', $localeCode)) {
            $localeCode = $this->getLocale();
        }

        $filePath = $this->getLocaleOverrideFile($localeCode, 'template'.DS.$type.DS.$file);
        if (empty($filePath) || !file_exists($filePath)) {
            return parent::getTemplateFile($file, $type, $localeCode);
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => Mage::getBaseDir('locale')));
 
        return (string) $ioAdapter->read($filePath);
    }

    /**
     * Custom function to return override folder for locales
     *
     * @param string $localeCode
     * @param string $fileName
     *
     * @return string
     */
    protected function getLocaleOverrideFile($localeCode, $fileName)
    {
        $store = null;
        if(!empty($this->_config['store'])) {
            $store = $this->_config['store'];
        }

        /** @see Yireo_EmailOverride_Helper_Data::getLocalOverrideFile */
        return Mage::helper('emailoverride')->getLocaleOverrideFile($localeCode, $fileName, $store);
    }

    /**
     * Loading data from module translation files
     *
     * @param string $moduleName
     * @param string $files
     * @param bool   $forceReload (optional)
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadModuleTranslation($moduleName, $files, $forceReload = false)
    {
        foreach ($files as $file) {
            $file = $this->_getModuleFilePath($moduleName, $file);
            $baseFile = basename($file);
            $overrideFile = Mage::getDesign()->getLocaleFileName($baseFile);
            if(file_exists($overrideFile)) {
                $file = $overrideFile;
            }
            $this->_addData($this->_getFileData($file), $moduleName, $forceReload);
        }
        return $this;
    }

    /**
     * Loading current theme translation
     *
     * @param bool $forceReload (optional)
     *
     * @return Mage_Core_Model_Translate
     */
    protected function _loadThemeTranslation($forceReload = false)
    {
        // Check for fallback support
        if (Mage::helper('emailoverride')->supportsDesignFallback() == false) {
            return parent::_loadThemeTranslation($forceReload);
        }

        // First add fallback package translate.csv files
        $fallbackModel = Mage::getModel('core/design_fallback');
        $designPackage = Mage::getSingleton('core/design_package');
        $fallbacks = $fallbackModel->getFallbackScheme($designPackage->getArea(), $designPackage->getPackageName(), $designPackage->getTheme('layout'));

        foreach ($fallbacks as $fallback)
        {
            if(!isset($fallback['_package']) || !isset($fallback['_theme'])) continue; // first one is empty for some reason
            $fallbackFile = $designPackage->getLocaleFileName('translate.csv', array('_package' => $fallback['_package']));
            $this->_addData($this->_getFileData($fallbackFile), false, $forceReload);
        }

        // Now add current package translate.csv
        $file = Mage::getDesign()->getLocaleFileName('translate.csv');
        $this->_addData($this->_getFileData($file), false, $forceReload);
        return $this;
    }
}
