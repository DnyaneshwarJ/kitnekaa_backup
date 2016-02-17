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
 * @package    Unirgy_DropshipVendorPromotions
 * @copyright  Copyright (c) 2011-2012 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

require_once "app/code/community/Unirgy/Dropship/controllers/VendorController.php";

class Unirgy_DropshipVendorPromotions_VendorController extends Unirgy_Dropship_VendorController
{
    public function indexAction()
    {
        $this->_forward('rules');
    }
    public function rulesAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $session->setUdpromoLastRulesGridUrl(Mage::getUrl('*/*/*', array('_current'=>true)));
        $this->_renderPage(null, 'udpromo');
    }
    public function checkRule()
    {
        $ruleId = Mage::app()->getRequest()->getParam('id');
        $vendorId = Mage::getSingleton('udropship/session')->getVendorId();
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('rule_id', $ruleId)
            ->addFieldToFilter('udropship_vendor', $vendorId);
        $collection->load();
        if (!$collection->getFirstItem()->getId()) {
            Mage::throwException('Rule Not Found');
        }
        return $this;
    }
    public function ruleEditAction()
    {
        $session = Mage::getSingleton('udropship/session');
        try {
            $this->checkRule();
            $this->_renderPage(null, 'udpromo');
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirectRuleAfterPost();
        }
    }
    public function ruleNewAction()
    {
        $this->_renderPage(null, 'udpromo');
    }
    public function rulePostAction()
    {
        $session = Mage::getSingleton('udropship/session');

        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model Mage_SalesRule_Model_Rule */
                $model = Mage::getModel('salesrule/rule');
                Mage::dispatchEvent(
                    'adminhtml_controller_salesrule_prepare_save',
                    array('request' => $this->getRequest()));
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId() || $model->getUdropshipVendor()!=$session->getVendorId()) {
                        Mage::throwException(Mage::helper('udropship')->__('Wrong rule specified.'));
                    }
                } else {
                    $cGroups = Mage::getResourceModel('customer/group_collection');
                    $data['customer_group_ids'] = array();
                    foreach ($cGroups as $cGroup) {
                        $data['customer_group_ids'][] = $cGroup->getId();
                    }
                    $websites = Mage::app()->getWebsites(true);
                    foreach ($websites as $website) {
                        $data['website_ids'][] = $website->getId();
                    }
                    $data['udropship_vendor'] = $session->getVendorId();
                }

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setUdpromoData($data);
                    $this->_redirect('*/*/ruleEdit', array('id'=>$model->getId()));
                    return;
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100,$data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $data['stop_rules_processing'] = 0;
                $data['coupon_code'] = $this->getRequest()->getParam('coupon_code');
                if (!empty($data['coupon_code'])) $data['coupon_type'] = Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC;
                $model->loadPost($data);

                $session->setUdpromoData($model->getData());

                $model->save();
                $session->addSuccess(Mage::helper('udropship')->__('The rule has been saved.'));
                $session->setUdpromoData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/ruleEdit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/ruleEdit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/ruleNew');
                }
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('udropship')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setUdpromoData($data);
                $this->_redirect('*/*/ruleEdit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $session->addError(Mage::helper('udropship')->__('Unable to find a data to save'));
        $this->_redirectRuleAfterPost();
    }
    protected function _redirectRuleAfterPost()
    {
        $session = Mage::getSingleton('udropship/session');
        if ($session->getUdpromoLastRulesGridUrl()) {
            $this->_redirectUrl($session->getUdpromoLastRulesGridUrl());
        } else {
            $this->_redirect('udpromo/vendor/rules');
        }
    }

    public function newActionHtmlAction()
    {
        Mage::register('is_udpromo_vendor',1);
        $this->_setTheme();
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('salesrule/rule'))
            ->setPrefix('actions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
    public function newConditionHtmlAction()
    {
        Mage::register('is_udpromo_vendor',1);
        $this->_setTheme();
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('salesrule/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
    public function chooserAction()
    {
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->getLayout()->createBlock(
                    'udpromo/adminhtml_promoWidgetChooserSku', 'promo_widget_chooser_sku',
                    array('js_form_object' => $request->getParam('form'),
                    ));
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', array());
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = array();
                }


                $block = $this->getLayout()->createBlock(
                    'udpromo/adminhtml_categoryCheckboxesTree', 'promo_widget_chooser_category_ids',
                    array('js_form_object' => $request->getParam('form'))
                )
                    ->setCategoryIds($ids)
                ;
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
    public function categoriesJsonAction()
    {
        Mage::helper('udropship/catalog')->setDesignStore(0, 'adminhtml');
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }
}