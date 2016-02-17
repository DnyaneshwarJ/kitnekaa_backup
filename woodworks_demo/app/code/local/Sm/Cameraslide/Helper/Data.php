<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 23/01/2015
 * Time: 23:34
 */
class Sm_Cameraslide_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_ENABLED_CAMERASLIDE   = 'cameraslide/general/enabled';
	const XML_USE_WIDGET            = 'cameraslide/general/use_widget';
	const XML_SLIDE_CAMERASLIDE     = 'cameraslide/general/slide';
	const XML_INCLUDE_JQUERY        = 'cameraslide/jquery/include_jquery';

	/*
	 *  Cameraslide item instance for lazy load
	 *
	 *  @var Sm_Cameraslide_Model_Data
	 * */
	protected $_cameraslideItemInstance;
	protected $_cameraslideItemSlidersInstance;

	public function enabledCameraslide($store = null)
	{
		return Mage::getStoreConfigFlag(self::XML_ENABLED_CAMERASLIDE, $store);
	}
	public function useWidget($store = null)
	{
		return Mage::getStoreConfigFlag(self::XML_USE_WIDGET, $store);
	}

	public function getSlide($store = null)
	{
		return Mage::getStoreConfig(self::XML_SLIDE_CAMERASLIDE, $store);
	}

	public function getIncludeJquery($store = null)
	{
		return Mage::getStoreConfig(self::XML_INCLUDE_JQUERY, $store);
	}

	public function getCameraslideItemInstance()
	{
		if(!$this->_cameraslideItemInstance)
		{
			$this->_cameraslideItemInstance = Mage::registry('slide');
			if(!$this->_cameraslideItemInstance)
			{
				Mage::throwException($this->__('Cameraslide item instance does not exit in Registry'));
			}
		}
		return $this->_cameraslideItemInstance;
	}

	public function getInlucdeJQquery()
	{
		if (!(int)$this->enabledCameraslide()) return;
		if (!defined('MAGENTECH_JQUERY') && (int)$this->getIncludeJquery()) {
			define('MAGENTECH_JQUERY', 1);
			$_jquery_libary = 'sm/cameraslide/js/jquery-2.1.3.min.js';
			return $_jquery_libary;
		}
	}

	public function getInlucdeNoconflict()
	{
		if (!(int)$this->enabledCameraslide()) return;
		if (!defined('MAGENTECH_JQUERY_NOCONFLICT') && (int)$this->getIncludeJquery()) {
			define('MAGENTECH_JQUERY_NOCONFLICT', 1);
			$_jquery_noconflict = 'sm/cameraslide/js/jquery-noconflict.js';
			return $_jquery_noconflict;
		}
	}

	public function getInlucdeMigrate()
	{
		if (!(int)$this->enabledCameraslide()) return;
		if (!defined('MAGENTECH_JQUERY_MIGRATE') && (int)$this->getIncludeJquery()) {
			define('MAGENTECH_JQUERY_MIGRATE', 1);
			$_jquery_noconflict = 'sm/cameraslide/js/jquery-migrate-1.2.1.min.js';
			return $_jquery_noconflict;
		}
	}

	public function randomInt()
	{
		return "_".uniqid(rand().time());
	}

	public function setSlideHtmlId($sId)
	{
		return "sm_cameraslide_{$sId}".$this->randomInt();
	}
	public function setSlideHtmlIdWrapper($wrapId)
	{
		return "sm_cameraslide_{$wrapId}_wrapper".$this->randomInt();
	}
}