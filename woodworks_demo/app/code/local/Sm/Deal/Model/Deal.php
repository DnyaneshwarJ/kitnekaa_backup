<?php
class Sm_Deal_Model_Deal extends Mage_Core_Model_Abstract{
	/**
	 * Entity code.
	 * Can be used as part of method name for entity processing
	 */
	const ENTITY= 'deal_deal';
	const CACHE_TAG = 'deal_deal';
	/**
	 * Prefix of model events names
	 * @var string
	 */
	protected $_eventPrefix = 'deal_deal';
	
	/**
	 * Parameter name in event
	 * @var string
	 */
	protected $_eventObject = 'deal';
	protected $_productInstance = null;
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author Ultimate Module Creator
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('deal/deal');
	}
	/**
	 * before save deal
	 * @access protected
	 * @return Sm_Deal_Model_Deal
	 * @author Ultimate Module Creator
	 */
	protected function _beforeSave(){
		parent::_beforeSave();
		$now = Mage::getSingleton('core/date')->gmtDate();
		if ($this->isObjectNew()){
			$this->setCreatedAt($now);
		}
		$this->setUpdatedAt($now);
		return $this;
	}
	/**
	 * get the url to the deal details page
	 * @access public
	 * @return string
	 * @author Ultimate Module Creator
	 */
	public function getDealUrl(){
		if ($this->getUrlKey()){
			return Mage::getUrl('', array('_direct'=>$this->getUrlKey()));
		}
		return Mage::getUrl('deal/deal/view', array('id'=>$this->getId()));
	}
	/**
	 * check URL key
	 * @access public
	 * @param string $urlKey
	 * @param bool $active
	 * @return mixed
	 * @author Ultimate Module Creator
	 */
	public function checkUrlKey($urlKey, $active = true){
		return $this->_getResource()->checkUrlKey($urlKey, $active);
	}
	/**
	 * save deal relation
	 * @access public
	 * @return Sm_Deal_Model_Deal
	 * @author Ultimate Module Creator
	 */
	protected function _afterSave() {
		$this->getProductInstance()->saveDealRelation($this);
		return parent::_afterSave();
	}
	/**
	 * get product relation model
	 * @access public
	 * @return Sm_Deal_Model_Deal_Product
	 * @author Ultimate Module Creator
	 */
	public function getProductInstance(){
		if (!$this->_productInstance) {
			$this->_productInstance = Mage::getSingleton('deal/deal_product');
		}
		return $this->_productInstance;
	}
	/**
	 * get selected products array
	 * @access public
	 * @return array
	 * @author Ultimate Module Creator
	 */
	public function getSelectedProducts(){
		if (!$this->hasSelectedProducts()) {
			$products = array();
			foreach ($this->getSelectedProductsCollection() as $product) {
				$products[] = $product;
			}
			$this->setSelectedProducts($products);
		}
		return $this->getData('selected_products');
	}
	/**
	 * Retrieve collection selected products
	 * @access public
	 * @return Sm_Deal_Resource_Deal_Product_Collection
	 * @author Ultimate Module Creator
	 */
	public function getSelectedProductsCollection(){
		$collection = $this->getProductInstance()->getProductCollection($this);
		return $collection;
	}
}