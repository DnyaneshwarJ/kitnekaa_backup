<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
class Unirgy_DropshipVendorProduct_Model_Source extends Unirgy_Dropship_Model_Source_Abstract
{
    const MEDIA_CFG_SHOW_EXPLICIT=1;
    const MEDIA_CFG_PER_OPTION_HIDDEN=2;
    public function isCfgUploadImagesSimple($store=null)
    {
        return Mage::getStoreConfigFlag('udprod/general/cfg_upload_images_simple', $store);
    }
    public function isMediaCfgPerOptionHidden($store=null)
    {
        return self::MEDIA_CFG_PER_OPTION_HIDDEN==Mage::getStoreConfig('udprod/general/cfg_show_media_gallery', $store);
    }
    public function isMediaCfgShowExplicit($store=null)
    {
        return self::MEDIA_CFG_SHOW_EXPLICIT==Mage::getStoreConfig('udprod/general/cfg_show_media_gallery', $store);
    }
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $prHlp = Mage::helper('udprod');

        switch ($this->getPath()) {

        case 'is_limit_categories':
            $options = array(
                0 => Mage::helper('udropship')->__('No'),
                1 => Mage::helper('udropship')->__('Enable Selected'),
                2 => Mage::helper('udropship')->__('Disable Selected'),
            );
            break;

        case 'udprod/general/cfg_show_media_gallery':
            $options = array(
                0 => Mage::helper('udropship')->__('No'),
                1 => Mage::helper('udropship')->__('Yes'),
                2 => Mage::helper('udropship')->__('Yes and hide per option upload'),
            );
            break;
        case 'udprod/quick_create_layout/cfg_attributes':
            $options = array(
                'one_column'      => Mage::helper('udropship')->__('One Column'),
                'separate_column' => Mage::helper('udropship')->__('Separate Columns'),
            );
            break;
        case 'udprod_unpublish_actions':
        case 'udprod/general/unpublish_actions':
            $options = array(
                'none'               => Mage::helper('udropship')->__('None'),
                'all'                => Mage::helper('udropship')->__('All'),
                'new_product'        => Mage::helper('udropship')->__('New Product'),
                'image_added'        => Mage::helper('udropship')->__('Image Added'),
                'image_removed'      => Mage::helper('udropship')->__('Image Removed'),
                'cfg_simple_added'   => Mage::helper('udropship')->__('Configurable Simple Added'),
                'cfg_simple_removed' => Mage::helper('udropship')->__('Configurable Simple Removed'),
                'attribute_changed'  => Mage::helper('udropship')->__('Attribute Value Changed'),
                'stock_changed'      => Mage::helper('udropship')->__('Stock Changed'),
                'custom_options_changed' => Mage::helper('udropship')->__('Custom Options Changed'),
            );
            break;
        case 'udprod_allowed_types':
        case 'udprod/general/allowed_types':
            $at = Mage::getStoreConfig('udprod/general/type_of_product');
            if (is_string($at)) {
                $at = unserialize($at);
            }
            $options = array(
                '*none*' => Mage::helper('udropship')->__('* None *'),
                '*all*'  => Mage::helper('udropship')->__('* All *'),
            );
            if (is_array($at)) {
                foreach ($at as $_at) {
                    $options[$_at['type_of_product']] = $_at['type_of_product'];
                }
            }
            break;
        case 'stock_status':
            $options = array(
                0 => Mage::helper('udropship')->__('Out of stock'),
                1 => Mage::helper('udropship')->__('In stock'),
            );
            break;
        case 'system_status':
            $options = array(
                1 => Mage::helper('udropship')->__('Published'),
                2 => Mage::helper('udropship')->__('Disabled'),
                3 => Mage::helper('udropship')->__('Under Review'),
                4 => Mage::helper('udropship')->__('Fix'),
                5 => Mage::helper('udropship')->__('Discard'),
            );
            break;

        case 'udprod/template_sku/type_of_product':
            $selector = true;
            $_options = Mage::getStoreConfig('udprod/general/type_of_product');
            if (!is_array($_options)) {
                $_options = unserialize($_options);
            }
            $options = array();
            if (!empty($_options) && is_array($_options)) {
                foreach ($_options as $opt) {
                    $_val = $opt['type_of_product'];
                    $options[$_val] = $_val;
                }
            }
            break;

        case 'product_websites':
            $collection = Mage::getModel('core/website')->getResourceCollection();
            $options = array('' => Mage::helper('udropship')->__('* None'));
            foreach ($collection as $w) {
                $options[$w->getId()] = $w->getName();
            }
            break;

        case 'udprod_backorders':
            $options = array();
            foreach (Mage::getSingleton('cataloginventory/source_backorders')->toOptionArray() as $opt) {
                $options[$opt['value']] = $opt['label'];
            }
            break;

        default:
            Mage::throwException(Mage::helper('udropship')->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>Mage::helper('udropship')->__('* Please select')) + $options;
        }

        return $options;
    }
}