<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AttributeController
 *
 * @author root
 */
class Amar_Profile_Adminhtml_Customer_AttributeController extends Mage_Adminhtml_Controller_Action
{
    
    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType("customer")->getTypeId();
    }
    
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    
    
    protected function _initAction()
    {
        $this->_title($this->__('Customers'))
             ->_title($this->__('Customer Attributes'))
             ->_title($this->__('Manage Customer Attributes'));

        if($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $this->loadLayout()
                ->_setActiveMenu('customer/customer_attrubute')
                ->_addBreadcrumb(Mage::helper('profile')->__('Customers'), Mage::helper('profile')->__('Customers'))
                ->_addBreadcrumb(
                    Mage::helper('profile')->__('Manage Customer Attributes'),
                    Mage::helper('profile')->__('Manage Customer Attributes'))
            ;
        }
        return $this;
    }

    

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $requested_attribute_code = $this->getRequest()->getParam("code");
        $model = Mage::getModel('profile/customer_attribute')->loadByCode($requested_attribute_code);
        $this->getRequest()->setParam("attribute_id",$model->getAttributeId());
        $id = $model->getId();
        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('profile')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('profile')->__('This attribute cannot be edited.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
        if (! empty($data)) {
            $model->addData($data);
        }

        Mage::register('entity_attribute', $model);

        $this->_initAction();
        
        $this->_title($id ? $model->getName() : $this->__('New Attribute'));

        $item = $id ? Mage::helper('profile')->__('Edit Customer Attribute')
                    : Mage::helper('profile')->__('New Customer Attribute');

        $this->_addBreadcrumb($item, $item);

        $this->getLayout()->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));

        $this->renderLayout();

    }

    
    
    
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('profile/customer_attribute')->loadByCode($attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('profile')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        if ($data) {
            /** @var $helperCatalog Mage_Catalog_Helper_Data */
            $helperProfile = Mage::helper('profile');
            //labels
            foreach ($data['frontend_label'] as & $value) {
                if ($value) {
                    $value = $helperProfile->stripTags($value);
                }
            }
        }
        return $data;
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('adminhtml/session');

            $redirectBack   = $this->getRequest()->getParam('back', false);
            /* @var $model Amar_Profile_Model_Customer_Attribute */
            $model = Mage::getModel('profile/customer_attribute');
            /* @var $helper Amar_Profile_Helper_Product */
            $helper = Mage::helper('profile/product');

            $id = $this->getRequest()->getParam('attribute_id');

            //validate attribute_code
            if (isset($data['attribute_code'])) {
                $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
                if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                    $session->addError(
                        Mage::helper('catalog')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.')
                    );
                    if($id)
                        $this->_redirect('*/*/edit', array('code' => $data['attribute_code'], '_current' => true));
                    else
                        $this->_redirect('*/*/edit', array('code' => '', '_current' => true));
                    return;
                }
            }


            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
                $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
                if (!$validatorInputType->isValid($data['frontend_input'])) {
                    foreach ($validatorInputType->getMessages() as $message) {
                        $session->addError($message);
                    }
                    if($id)
                        $this->_redirect('*/*/edit', array('code' => $data['attribute_code'], '_current' => true));
                    else
                        $this->_redirect('*/*/edit', array('code' => '', '_current' => true));
                    return;
                }
            }

            if ($id) {
                $model->load($id);

                if (!$model->getId()) {
                    $session->addError(
                        Mage::helper('profile')->__('This Attribute no longer exists'));
                    $this->_redirect('*/*/');
                    return;
                }

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $session->addError(
                        Mage::helper('catalog')->__('This attribute cannot be updated.'));
                    $session->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                * @todo add to helper and specify all relations for properties
                */
                $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
            }

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }
            if (!isset($data['is_filterable'])) {
                $data['is_filterable'] = 0;
            }
            if (!isset($data['is_filterable_in_search'])) {
                $data['is_filterable_in_search'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            

            //filter
            $data = $this->_filterPostData($data);
            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            
            $customer = Mage::getModel('customer/customer');
            $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
            
            $model->setAttributeSetId($attrSetId);
            $model->setAttributeGroupId('General');
            
            
            try {
                $model->save();
                
                //code to update the data of profile
                $profileCollection = Mage::getModel("profile/profile")->getCollection()
                        ->addFieldtoFilter("attribute_code",$model->getAttributeCode())
                        ->addFieldtoFilter("attribute_id",$model->getAttributeId());
                $profileModel = Mage::getModel("profile/profile");
                
                if($profileCollection->getSize() != 0)
                {
                    foreach($profileCollection as $_profile)
                    {
                        $profileModel = $profileModel->load($_profile->getId());
                        break;
                    }
                }
                $profileModel->setAttributeCode($model->getAttributeCode())
                             ->setAttributeId($model->getAttributeId())
                             ->setSortOrder($model->getSortOrder())
                             ->save();
                //code to update the data of the profile ends here
                
                Mage::getSingleton('eav/config')
                ->getAttribute('customer', $model->getAttributeCode())
                ->setData('used_in_forms', array(
                                                 'adminhtml_customer',
                                                 'customer_account_create',
                                                 'customer_account_edit',
                                                 'checkout_register'
                                                )
                         )->save();
                
                
                $session->addSuccess(
                    Mage::helper('catalog')->__('The customer attribute has been saved.'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                $session->setAttributeData(false);
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('code' => $model->getAttributeCode(),'_current'=>true));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setAttributeData($data);
                if($id)
                    $this->_redirect('*/*/edit', array('code' => $model->getAttributeCode(), '_current' => true));
                else
                    $this->_redirect('*/*/edit', array('code' => '', '_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) 
        {
            //$code = $this->getRequest()->getParam('attribute_code');
            $model = Mage::getModel('profile/customer_attribute')->load($id);

            // entity type check
            
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('profile')->__('This attribute cannot be deleted.'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                
                //code to update the data of profile
                
                $profileCollection = Mage::getModel("profile/profile")->getCollection()
                        ->addFieldtoFilter("attribute_code",$model->getAttributeCode())
                        ->addFieldtoFilter("attribute_id",$model->getAttributeId());
                
                if($profileCollection->getSize() > 0)
                {
                    foreach($profileCollection as $_profile)
                    {
                        $_profile->delete();
                    }
                }
                
                //code to update the data of the profile ends here
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('profile')->__('The customer attribute has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('code' => $this->getRequest()->getParam('attribute_code')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('profile')->__('Unable to find an attribute to delete.'));
        $this->_redirect('*/*/');
    }
    
    
}

?>
