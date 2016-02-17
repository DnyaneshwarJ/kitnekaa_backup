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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist Text collection
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Model_Resource_Text_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
       /**
     * Store Ids array
     *
     * @var array
     */
    protected $_storeIds = array();

   

    /**
     * Sum of items collection qty
     *
     * @var int
     */
    protected $_itemsQty;

  
    /**
     * Customer website ID
     *
     * @var int
     */
    protected $_websiteId = null;

    /**
     * Customer group ID
     *
     * @var int
     */
    protected $_customerGroupId = null;


    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('wishlist/text');
        $this->addFilterToMap('store_id', 'main_table.store_id');
    }

    /**
     * After load processing
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->resetItemsDataChanged();

        $this->getPageSize();

        return $this;
    }


    /**
     * Add filter by wishlist object
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addWishlistFilter(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        $this->addFieldToFilter('wishlist_id', $wishlist->getId());
        return $this;
    }

    /**
     * Add filtration by customer id
     *
     * @param int $customerId
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()
            ->join(
                array('wishlist' => $this->getTable('wishlist/wishlist')),
                'main_table.wishlist_id = wishlist.wishlist_id',
                array()
            )
            ->where('wishlist.customer_id = ?', $customerId);
        return $this;
    }

    /**
     * Add filter by shared stores
     *
     * @param array $storeIds
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addStoreFilter($storeIds = array())
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        $this->_storeIds = $storeIds;
        $this->addFieldToFilter('store_id', array('in' => $this->_storeIds));

        return $this;
    }

    /**
     * Add items store data to collection
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addStoreData()
    {
        $storeTable = Mage::getSingleton('core/resource')->getTableName('core/store');
        $this->getSelect()->join(array('store'=>$storeTable), 'main_table.store_id=store.store_id', array(
            'store_name'=>'name',
            'item_store_id' => 'store_id'
        ));
        return $this;
    }

    /**
     * Add wishlist sort order
     *
     * @deprecated after 1.6.0.0-rc2
     * @see Varien_Data_Collection_Db::setOrder() is used instead
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function addWishListSortOrder($attribute = 'added_at', $dir = 'desc')
    {
        $this->setOrder($attribute, $dir);
        return $this;
    }

    /**
     * Reset sort order
     *
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function resetSortOrder()
    {
        $this->getSelect()->reset(Zend_Db_Select::ORDER);
        return $this;
    }

 



    /**
     * Get sum of items collection qty
     *
     * @return int
     */
    public function getItemsQty(){
        if (is_null($this->_itemsQty)) {
            $this->_itemsQty = 0;
            foreach ($this as $wishlistItem) {
                $qty = $wishlistItem->getQty();
                $this->_itemsQty += ($qty === 0) ? 1 : $qty;
            }
        }

        return (int)$this->_itemsQty;
    }

    /**
     * Setter for $_websiteId
     *
     * @param int $websiteId
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $websiteId;
        return $this;
    }

    /**
     * Setter for $_customerGroupId
     *
     * @param int $customerGroupId
     * @return Mage_Wishlist_Model_Resource_Item_Collection
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->_customerGroupId = $customerGroupId;
        return $this;
    }
    public function addStatusFilter()
    {
    	$this->addFieldToFilter('status', 1);
    	return $this;
    }
}
