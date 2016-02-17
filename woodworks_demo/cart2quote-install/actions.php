<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2015] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2015 Cart2Quote B.V. (http://www.cart2quote.com)
 * @license     http://www.cart2quote.com/ordering-licenses
 */

require_once '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
require_once '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';


if (isset($_POST['url'])) {
    if (strpos($_POST['url'], '/cart2quote-install/preinstall/') !== false) {
        $perform = true;
    } else {
        $perform = false;
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'disable_cache':
                if ($perform === true) {
                    echo disableCache();
                } else {
                    echo isCacheDisabled();
                }
                break;
            case 'disable_compiler':
                if ($perform === true) {
                    echo disableCompiler();
                } else {
                    echo isCompilerDisabled();
                }
                break;
            case 'clean_cache':
                echo cleanCache();
                break;
            default:
                $action = null;
                break;
        }
    } else {
        $action = null;
    }
}


function isCacheDisabled()
{
    Mage::app('admin');
    $options = Mage::getModel('core/cache')->canUse();
    foreach ($options as $option) {
        if ($option == 1)
            return false;
    }

    return true;
}

function disableCache()
{
    try {
        Mage::app('admin');
        $model = Mage::getModel('core/cache');
        $options = $model->canUse();
        foreach ($options as $option => $value) {
            $options[$option] = 0;
        }
        $model->saveOptions($options);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function isCompilerDisabled()
{
    if (defined('COMPILER_INCLUDE_PATH') || defined('COMPILER_COLLECT_PATH')) {
        return false;
    } else {
        return true;
    }
}

function disableCompiler()
{
    try {
        Mage::app('admin');
        Mage::getModel('compiler/process')->registerIncludePath(false);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function cleanCache()
{
    try {
        Mage::app()->cleanCache();
        Mage::app()->clearWebsiteCache();
        Mage::app()->cleanAllSessions();
        Mage::app()->getCacheInstance()->flush();
        Mage::getModel('catalog/product_image')->clearCache();
        Mage::getModel('core/design_package')->cleanMergedJsCss();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
