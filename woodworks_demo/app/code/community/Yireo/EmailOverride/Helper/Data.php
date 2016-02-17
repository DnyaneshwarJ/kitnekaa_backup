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
 * EmailOverride helper
 */
class Yireo_EmailOverride_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param string                               $localeCode
     * @param string                               $fileName
     * @param string|integer|Mage_Core_Model_Store $store (optional)
     *
     * @return string|null
     */
    public function getLocaleOverrideFile($localeCode, $fileName, $store = null)
    {
        $paths = $this->getLocalePaths($store);
        
        $localCodes = $localeCode === 'en_US'
            ? array($localeCode)
            : array($localeCode, 'en_US');

        foreach ($localCodes as $localeCode) {
            foreach ($paths as $path) {
                $filePath = $path . DS . $localeCode . DS . $fileName;
                if (!empty($filePath) && file_exists($filePath)) {
                    return $filePath;
                }
            }
        }

        return null;
    }

    /**
     * @param string|integer|Mage_Core_Model_Store $store (optional)
     *
     * @return array
     */
    public function getLocalePaths($store = null)
    {
        $paths = array();

        $design = $this->getDesign($store);
        $paths[] = Mage::getBaseDir('design').DS.'frontend'.DS.$design['package'].DS.$design['theme'].DS.'locale';

        // Check for fallback support
        if ($this->supportsDesignFallback()) {
            $fallbackModel = Mage::getModel('core/design_fallback');
            if(!empty($fallbackModel)) {
                $fallbackSchemes = $fallbackModel->getFallbackScheme('frontend', $design['package'], $design['theme']);
                if(!empty($fallbackSchemes)) {
                    foreach($fallbackSchemes as $scheme) {
                        if(!isset($scheme['_package']) || !isset($scheme['_theme'])) continue;
                        $paths[] = Mage::getBaseDir('design').DS.'frontend'.DS.$scheme['_package'].DS.$scheme['_theme'].DS.'locale';
                    }
                }
            }
        }
    
        $paths[] = Mage::getBaseDir('design').DS.'frontend'.DS.$design['package'].DS.'default'.DS.'locale';
        $paths[] = Mage::getBaseDir('design').DS.'frontend'.DS.'default'.DS.'default'.DS.'locale';
        $paths[] = Mage::getBaseDir('design').DS.'frontend'.DS.'base'.DS.'default'.DS.'locale';
        $paths[] = Mage::getBaseDir('locale');

        return $paths;
    }

    /**
     * @param string|integer|Mage_Core_Model_Store $store (optional)
     *
     * @return array
     */
    public function getDesign($store = null)
    {
        if(empty($store)) {
            $store = Mage::registry('emailoverride.store');
        }

        $packageName = null;
        $theme = null;

        if (Mage::app()->getStore()->isAdmin() == false) {
            $package = Mage::getSingleton('core/design_package');
            $originalArea = $package->getArea();
            $originalStore = $package->getStore();

            if(!empty($store)) $package->setStore($store);
            $package->setArea('frontend');
            $packageName = $package->getPackageName();
            $theme = $package->getTheme('default');

            $package->setArea($originalArea);
            $package->setStore($originalStore);
        }

        if(empty($packageName) || in_array($theme, array('base', 'default'))) {
            $packageName = Mage::getStoreConfig('design/package/name', $store);
        }
        
        if(empty($theme)) {
            $theme = Mage::getStoreConfig('design/theme/default', $store);
        }

        if(empty($theme) || in_array($theme, array('default'))) {
            $theme = Mage::getStoreConfig('design/theme/locale', $store);
        }

        if(empty($packageName)) $packageName = 'default';
        if(empty($theme)) $theme = 'default';

        return array(
            'package' => $packageName,
            'theme' => $theme,
        );
    }

    /**
     * @return boolean
     */
    public function supportsDesignFallback()
    {
        // Check for the right file
        if (file_exists(BP . '/app/code/core/Mage/Core/Model/Design/Fallback.php') == false) {
            return false;
        }

        return true;
    }
}
