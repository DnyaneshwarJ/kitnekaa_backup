<?php
/*------------------------------------------------------------------------
 # SM Search Box Pro - Version 1.0
 # Copyright (c) 2013 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Searchboxpro_Block_List extends Mage_Catalog_Block_Product_Abstract
{

	protected $_config = null;

	public function __construct($attributes = array()){
		parent::__construct();
		$this->_config = Mage::helper('searchboxpro/data')->get($attributes);
	}

	public function getConfig($name=null, $value=null){
		if (is_null($this->_config)){
			$this->_config = Mage::helper('searchboxpro/data')->get(null);
		}
		if (!is_null($name) && !empty($name)){
			$valueRet = isset($this->_config[$name]) ? $this->_config[$name] : $value;
			return $valueRet;
		}
		return $this->_config;
	}

	public function setConfig($name, $value=null){
		if (is_null($this->_config)) $this->getConfig();
		if (is_array($name)){
			Mage::log($name);
			$this->_config = array_merge($this->_config, $name);
			return;
		}
		if (!empty($name)){
			$this->_config[$name] = $value;
		}
		return true;
	}

	public function getConfigObject(){
        return (object)$this->getConfig();
	}

	protected function _toHtml(){
		if(!$this->getConfig('isenabled')) return;
		$template_file = 'sm/searchboxpro/default.phtml';
		$this->setTemplate($template_file);
		return parent::_toHtml();
	}

	
	
	public function getCategories(){
        $category = Mage::getModel('searchboxpro/system_config_source_listCategory');
		$cat_list = $category->toOptionArray(true);
        return $cat_list;	
	}
	
	
	/*
    public function getSearchableCategories()
    {
        $rootCategory = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
		$list_cat = $this->getSearchableSubCategories($rootCategory);
        return $list_cat;
    }

    public function getSearchableSubCategories($category)
    {
        return Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('include_in_menu', 1)
            ->addIdFilter($category->getChildren())
            ->setOrder('position', 'ASC')
            ->load();
    }
	*/

    protected $_terms;
    protected $_minPopularity;
    protected $_maxPopularity;


    /**
     * Load terms and try to sort it by names
     *
     * @return Mage_CatalogSearch_Block_Term
     */
    protected function _loadTerms()
    {
        if (empty($this->_terms)) {
            $this->_terms = array();
			
			
			$is_ajax = Mage::app()->getRequest()->getParam('is_ajax');
			
			//Zend_debug::dump($is_ajax);
			if($is_ajax){
				//$count_term = $this->getConfig('limit_popular');
				 $count_term = Mage::app()->getRequest()->getParam('count_term');
				 $terms = Mage::getResourceModel('catalogsearch/query_collection')
                ->setPopularQueryFilter(Mage::app()->getStore()->getId())
                ->setPageSize( $count_term )
                ->load()
                ->getItems();
			}else{
			$count_term = $this->getConfig('limit_popular');
            $terms = Mage::getResourceModel('catalogsearch/query_collection')
                ->setPopularQueryFilter(Mage::app()->getStore()->getId())
                ->setPageSize( $count_term )
                ->load()
                ->getItems();
			}
            if( count($terms) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($terms)->getPopularity();
            $this->_minPopularity = end($terms)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($terms as $term) {
                if( !$term->getPopularity() ) {
                    continue;
                }
                $term->setRatio(($term->getPopularity()-$this->_minPopularity)/$range);
                $temp[$term->getName()] = $term;
                $termKeys[] = $term->getName();
            }
            natcasesort($termKeys);

            foreach ($termKeys as $termKey) {
                $this->_terms[$termKey] = $temp[$termKey];
            }
        }
        return $this;
    }

    public function getTerms()
    {
        $this->_loadTerms();
        return $this->_terms;
    }

    public function getSearchUrl($obj)
    {
        $url = Mage::getModel('core/url');
        /*
        * url encoding will be done in Url.php http_build_query
        * so no need to explicitly called urlencode for the text
        */
        $url->setQueryParam('q', $obj->getName());
        return $url->getUrl('catalogsearch/result');
    }

    public function getMaxPopularity()
    {
        return $this->_maxPopularity;
    }

    public function getMinPopularity()
    {
        return $this->_minPopularity;
    }
	
	public function getScriptTags(){
		$import_str = "";
		$jsHelper = Mage::helper('core/js');
		if (null == Mage::registry('jsmart.jquery')){
			// jquery has not added yet
			//if (Mage::getStoreConfigFlag('responsivelistting_cfg/advanced/include_jquery')){
				// if module allowed jquery.
				//$import_str .= $jsHelper->includeSkinScript('sm/searchboxpro/js/jquery-1.8.2.min.js');
				Mage::register('jsmart.jquery', 1);
			//}
		}
		if (null == Mage::registry('jsmart.jquerynoconfict')){
			// add once noConflict
			//$import_str .= $jsHelper->includeSkinScript('sm/searchboxpro/js/jquery-noconflict.js');
			Mage::register('jsmart.jquerynoconfict', 1);
		}
		
		/*if (null == Mage::registry('jsmart.responsivelistting.js')){
			// add script for this module.
			$import_str .= $jsHelper->includeSkinScript('sm/responsivelistting/js/jquery.isotope.js');
			Mage::register('jsmart.responsivelistting.js', 1);
		}*/
		return $import_str;
	}

}



