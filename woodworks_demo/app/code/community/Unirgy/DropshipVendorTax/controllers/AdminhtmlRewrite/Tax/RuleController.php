<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax rule controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
include_once 'app/code/core/Mage/Adminhtml/controllers/Tax/RuleController.php';

class Unirgy_DropshipVendorTax_AdminhtmlRewrite_Tax_RuleController extends Mage_Adminhtml_Tax_RuleController
{
    protected function _isValidRuleRequest($ruleModel)
    {
        $existingRules = $ruleModel->fetchUdRuleCodes($ruleModel->getTaxRate(),
            $ruleModel->getTaxCustomerClass(), $ruleModel->getTaxProductClass(), $ruleModel->getTaxVendorClass());

        //Remove the current one from the list
        $existingRules = array_diff($existingRules, array($ruleModel->getCode()));
        $existingRules = array_filter($existingRules);

        //Verify if a Rule already exists. If not throw an error
        if (count($existingRules) > 0) {
            $ruleCodes = implode(",", $existingRules);
            $this->_getSingletonModel('adminhtml/session')->addError(
                $this->_getHelperModel('tax')->__('Rules (%s) already exist for the specified Tax Rate, Customer Tax Class and Product Tax Class combinations', $ruleCodes));
            return false;
        }
        return true;
    }
}
