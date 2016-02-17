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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Session extends Mage_Core_Model_Session_Abstract
{
    protected $_vendor;

    public function __construct()
    {
        $namespace = 'udropship';
        $this->init($namespace);
        Mage::dispatchEvent('udropship_session_init', array('session'=>$this));
    }

    public function setVendor($vendor)
    {
        $this->_vendor = $vendor;
        return $this;
    }

    public function getVendor()
    {
        if ($this->_vendor instanceof Unirgy_Dropship_Model_Vendor) {
            return $this->_vendor;
        }

        if ($this->getId()) {
            $vendor = Mage::helper('udropship')->getVendor($this->getId());
        } else {
            $vendor = Mage::getModel('udropship/vendor');
        }
        $this->setVendor($vendor);

        return $this->_vendor;
    }

    public function getVendorId()
    {
        return $this->getId();
    }

    public function isLoggedIn()
    {
        return (bool)$this->getId() && (bool)$this->getVendor()->getId();
    }


    public function setVendorAsLoggedIn($vendor)
    {
        $this->setVendor($vendor);
        $this->setId($vendor->getId());
        Mage::dispatchEvent('udropship_vendor_login', array('vendor'=>$vendor));
        return $this;
    }

    public function login($username, $password)
    {
        $vendor = Mage::getModel('udropship/vendor');

        if ($vendor->authenticate($username, $password)) {
            $this->setVendorAsLoggedIn($vendor);
            return true;
        }
        return false;
    }

    public function loginById($vendorId)
    {
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        if ($vendor->getId()) {
            $this->setVendorAsLoggedIn($vendor);
            return true;
        }
        return false;
    }

    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->setId(null);
            Mage::dispatchEvent('udropship_vendor_logout', array('vendor'=>$this->getVendor()));
        }
        return $this;
    }

    public function authenticate(Mage_Core_Controller_Varien_Action $action, $loginUrl = null)
    {
        if (!$this->isLoggedIn()) {
            $this->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current'=>true)));
            if (is_null($loginUrl)) {
                $loginUrl = Mage::getUrl('udropship/vendor/login');
            }
            $action->getResponse()->setRedirect($loginUrl);
            return false;
        }
        return true;
    }

    public function loginPostRedirect($action)
    {
        if (!$this->getBeforeAuthUrl() || $this->getBeforeAuthUrl() == Mage::getBaseUrl() ) {
            $this->setBeforeAuthUrl(Mage::getUrl('udropship/vendor'));
            if ($this->isLoggedIn()) {
                if ($action->getRequest()->getActionName()=='noRoute') {
                    $this->setBeforeAuthUrl(Mage::getUrl('*/*'));
                } else {
                    $this->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current'=>true)));
                }
                if ($this->getAfterAuthUrl()) {
                    $this->setBeforeAuthUrl($this->getAfterAuthUrl(true));
                }
            }
        } else if ($this->getBeforeAuthUrl() == Mage::getUrl('udropship/vendor/logout')) {
            $this->setBeforeAuthUrl(Mage::getUrl('udropship/vendor'));
        } else {
            if (!$this->getAfterAuthUrl()) {
                $this->setAfterAuthUrl($this->getBeforeAuthUrl());
            }
            if ($this->isLoggedIn()) {
                $this->setBeforeAuthUrl($this->getAfterAuthUrl(true));
            }
        }
        $action->getResponse()->setRedirect($this->getBeforeAuthUrl(true));
    }
}
