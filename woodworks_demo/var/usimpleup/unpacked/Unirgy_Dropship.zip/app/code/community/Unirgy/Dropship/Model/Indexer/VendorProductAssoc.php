<?php

class Unirgy_Dropship_Model_Indexer_VendorProductAssoc extends Mage_Index_Model_Indexer_Abstract
{
    const EVENT_MATCH_RESULT_KEY = 'udropship_vendor_product_assoc_match_result';
    const EVENT_TYPE_REINDEX_ASSOC = 'udropship_vendor_reindex_assoc';

    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),
        Unirgy_Dropship_Model_Vendor::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            self::EVENT_TYPE_REINDEX_ASSOC,
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    protected function _construct()
    {
        $this->_init('udropship/indexer_vendorProductAssoc');
    }

    public function getName()
    {
        return Mage::helper('udropship')->__('Unirgy Vendor-Product Associations Indexer');
    }

    public function getDescription()
    {
        return Mage::helper('udropship')->__('Unirgy Vendor-Product Associations Indexer');
    }

    public function matchEvent(Mage_Index_Model_Event $event)
    {
        $data       = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $result = parent::matchEvent($event);

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();

        if ($entity == Mage_Catalog_Model_Convert_Adapter_Product::ENTITY) {
            $event->addNewData('catalog_product_price_reindex_all', true);
        } else if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $this->_registerCatalogProductSaveEvent($event);
                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    $this->_registerCatalogProductMassActionEvent($event);
                    break;
            }
        } else if ($entity == Unirgy_Dropship_Model_Vendor::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $this->_registerVendorSaveEvent($event);
                    break;
                case self::EVENT_TYPE_REINDEX_ASSOC:
                    $event->addNewData('udreindex_vendor_ids', $event->getDataObject()->getVendorIds());
                    break;

            }
        }
    }

    protected function _registerVendorSaveEvent(Mage_Index_Model_Event $event)
    {
        $vendor = $event->getDataObject();
        $event->addNewData('udreindex_vendor_ids', array($vendor->getId()));
    }

    protected function _registerCatalogProductSaveEvent(Mage_Index_Model_Event $event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product      = $event->getDataObject();
        $event->addNewData('udreindex_product_ids', array($product->getId()));
    }

    protected function _registerCatalogProductMassActionEvent(Mage_Index_Model_Event $event)
    {
        /* @var $actionObject Varien_Object */
        $actionObject = $event->getDataObject();
        $event->addNewData('udreindex_product_ids', $actionObject->getProductIds());
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['udreindex_product_ids'])) {
            $this->_getResource()->beginTransaction();
            try {
                $this->_getResource()->reindexProducts($data['udreindex_product_ids']);
                $this->_getResource()->commit();
            } catch (Exception $e) {
                $this->_getResource()->rollBack();
                throw $e;
            }
        }
        if (!empty($data['udreindex_vendor_ids'])) {
            $this->_getResource()->beginTransaction();
            try {
                $this->_getResource()->reindexVendors($data['udreindex_vendor_ids']);
                $this->_getResource()->commit();
            } catch (Exception $e) {
                $this->_getResource()->rollBack();
                throw $e;
            }
        }
        if (empty($data['catalog_product_price_skip_call_event_handler'])) {
            $this->callEventHandler($event);
        }
    }
}