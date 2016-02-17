<?php

class Kithnekaa_Solr_Model_Engine extends JeroenVermeulen_Solarium_Model_Engine
{

	
    /**
     * Rebuild the index. If a Store ID or Product IDs are specified it is only rebuilt for those.
     *
     * @param int|null $storeId - Store View Id
     * @param int[]|null $productIds - Product Entity Id(s)
     * @return bool                  - True on success
     */
    public function rebuildIndex( $storeId = null, $productIds = null ) {
        if ( !$this->_working ) {
            return false;
        }
        $result = false;
        try {
            $coreResource = Mage::getSingleton( 'core/resource' );
            $readAdapter  = $coreResource->getConnection( 'core_read' );

            $select = $readAdapter->select();
            $select->from( $coreResource->getTableName( 'catalogsearch/fulltext' ),
                           array( 'product_id', 'store_id', 'data_index', 'fulltext_id' ) );

            if ( empty( $storeId ) ) {
                $select->where( 'store_id IN (?)', $this->getEnabledStoreIds() );
            } else {
                $select->where( 'store_id', $storeId );
            }
            if ( !empty( $productIds ) ) {
                if ( is_numeric( $productIds ) ) {
                    $select->where( 'product_id = ?', $productIds );
                } else {
                    if ( is_array( $productIds ) ) {
                        $select->where( 'product_id IN (?)', $productIds );
                    }
                }
            }
            $products = $readAdapter->query( $select );

            if ( !$products->rowCount() ) {
                // No matching products, nothing to update, consider OK.
                $result = true;
            } else {
                /** @var Solarium\Plugin\BufferedAdd\BufferedAdd $buffer */
                $buffer = $this->_client->getPlugin( 'bufferedadd' );
                $buffer->setBufferSize( max( 1, $this->getConf( 'reindexing/buffersize', $storeId ) ) );
                $buffer->setEndpoint( 'update' );
                /** @noinspection PhpAssignmentInConditionInspection */
                while ( $product = $products->fetch() ) {
                	
                	$product_details_for_index = $this->addProductDetailsForSolarIndex($product[ 'store_id' ], $product[ 'product_id' ]);
                	
                    $data = array( 'id' => intval( $product[ 'fulltext_id' ] ),
                                   'product_id' => intval( $product[ 'product_id' ] ),
                                   'store_id' => intval( $product[ 'store_id' ] ),
                    			   'category_id' => $product_details_for_index['product_category_id'],
                    			   'category_name' => $product_details_for_index['product_category_name'],
                    			   'product_name' => $product_details_for_index['product_name'],
                    			   'product_url'  => $product_details_for_index['product_url'],
                    		       'product_manufacturer'  => $product_details_for_index['product_manufacturer'],
                    			   'product_price'  => (float) $product_details_for_index['product_price'],
                    			   'is_featured' => $product_details_for_index['featured_product'],
                                   'text' => $this->_filterString( $product[ 'data_index' ] ) );
                    $buffer->createDocument( $data );
                }
                $solariumResult = $buffer->flush();
                $this->optimize(); // ignore result
                $result = $this->_processResult( $solariumResult, 'flushing buffered add' );
            }
        } catch ( Exception $e ) {
            $this->_lastError = $e;
            Mage::log( sprintf( '%s->%s: %s', __CLASS__, __FUNCTION__, $e->getMessage() ), Zend_Log::ERR );
        }
        return $result;
    }
    
    
    /*added by anthony*/
    public function addProductDetailsForSolarIndex($storeId = null, $productId = null){
    	$root_category_id = Mage::app()->getStore($storeId)->getRootCategoryId();
    	
    	$product_details = array();
    	
    	$product_category = "";
    	
    	$product = Mage::getModel('catalog/product')->load($productId);
    	$cats = $product->getCategoryIds();
    	
    	foreach ($cats as $category_id) {
    		$category = Mage::getModel('catalog/category')->load($category_id);
    	
    		$current_category = $category;
    		//$product_category = $category->getName();
    		$product_category_id = array();
    		array_push($product_category_id, $category->getId());
    		
    		$product_category_name = array();
    		array_push($product_category_name, $category->getName());//
    		
    		$counter_category_loop = 1;
    	
    		$current_category_id = $current_category->getId();
    		$previous_category_id = 0;
    	
    		while(($current_category->getParentCategory()) && ($current_category->getParentCategory()->getId() !== $root_category_id) && (($counter_category_loop === 1) || ($previous_category_id !== $current_category_id))){
    				
    			if($current_category->getParentCategory()->getName()){
    				//$product_category .= " >>> ".$current_category->getParentCategory()->getName();
    				array_push($product_category_id,$current_category->getParentCategory()->getId());//
    				array_push($product_category_name,$current_category->getParentCategory()->getName());
    			}
    				
    			$previous_category = $current_category;
    			$previous_category_id = $previous_category->getParentCategory()->getId();
    				
    			$current_category = $current_category->getParentCategory();
    			$current_category_id  = $current_category->getParentCategory()->getId();
    			$counter_category_loop++;
    		}
    	}
    	
    	$product_details['product_category_id'] = $product_category_id;
    	$product_details['product_category_name'] = $product_category_name;
    	$product_details['product_name'] = $product->getName();
    	$product_details['product_url'] = $product->getProductUrl();
    	$product_details['product_manufacturer'] = $product->getAttributeText('manufacturer');
    	$product_details['product_price'] = (float) $product->getPrice();
    	$product_details['featured_product'] = $product->getFeatured();
    	return $product_details;
    }


    public function query( $storeId, $queryString, $try = 1 ) {
    	if ( !$this->_working ) {
    		return false;
    	}
    	$result = false;
    	try {
    		$query = $this->_client->createSelect();
    		// Default field, needed when it is not specified in solrconfig.xml
    		$query->addParam( 'df', 'text' );
    		$query->setQuery( $this->_filterString( $queryString ) );
    		$query->setRows( $this->getConf( 'results/max' ) );
    		$query->setFields( array( 'product_id', 'score','product_name') );
    		if ( is_numeric( $storeId ) ) {
    			$query->createFilterQuery( 'store_id' )->setQuery( 'store_id:' . intval( $storeId ) );
    			
    		}
    		/*
    		if(Mage::app()->getRequest()->getParam('cat', false)){
    			echo "Success"; exit;
    		} else {
    			echo "fail"; exit;
    		}
    		*/
    		
    		$query->addSort( 'is_featured', $query::SORT_DESC );
    		$query->addSort( 'score', $query::SORT_DESC );
    		
    		$doAutoCorrect = ( 1 == $try && $this->getConf( 'results/autocorrect' ) );
    		if ( $doAutoCorrect ) {
    			$spellCheck = $query->getSpellcheck();
    			$spellCheck->setQuery( $queryString );
    			// You need Solr >= 4.0 for this to improve spell correct results.
    			$query->addParam( 'spellcheck.alternativeTermCount', 1 );
    		}
    		$query->setTimeAllowed( intval( $this->getConf( 'server/search_timeout' ) ) );
    		$solrResultSet        = $this->_client->select( $query );
    		$this->_lastQueryTime = $solrResultSet->getQueryTime();
    		$result               = array();
    		foreach ( $solrResultSet as $solrResult ) {
    			$result[ ] = array( 'relevance' => $solrResult[ 'score' ],
    					'product_id' => $solrResult[ 'product_id' ]);
    		}
    
    		$correctedQueryString = false;
    		if ( $doAutoCorrect ) {
    			$spellCheckResult = $solrResultSet->getSpellcheck();
    			if ( $spellCheckResult && !$spellCheckResult->getCorrectlySpelled() ) {
    				$collation = $spellCheckResult->getCollation();
    				if ( $collation ) {
    					$correctedQueryString = $collation->getQuery();
    				}
    				if ( empty( $correctedQueryString ) ) {
    					$suggestions = $spellCheckResult->getSuggestions();
    					if ( !empty( $suggestions ) ) {
    						$words = array();
    						/** @var Solarium\QueryType\Select\Result\Spellcheck\Suggestion $suggestion */
    						foreach ( $suggestions as $suggestion ) {
    							$words[] = $suggestion->getWord();
    						}
    						$correctedQueryString = implode( ' ', $words );
    					}
    				}
    				if ( !empty( $correctedQueryString ) ) {
    					// Add results from auto correct
    					$result = array_merge( $result, $this->query( $storeId, $correctedQueryString, $try + 1 ) );
    				}
    			}
    		}
    	} catch ( Exception $e ) {
    		$this->_lastError = $e;
    		Mage::log( sprintf( '%s->%s: %s', __CLASS__, __FUNCTION__, $e->getMessage() ), Zend_Log::ERR );
    	}
    	return $result;
    }
    
    /**
     * @param integer $storeId - Store View Id
     * @param string $queryString - What the user is typing
     * @return null|string
     */
    
    public function getAutoSuggestions_( $storeId, $queryString ) {
        //create basic query with wildcard
        $query = $this->_client->createSelect();
        $query->setFields('text');
        $query->setQuery( $queryString . '*' );
        $query->setRows(0);

        if ( is_numeric( $storeId ) ) {
            $query->createFilterQuery( 'store_id' )->setQuery( 'store_id:' . intval( $storeId ) );
        }

        $groupComponent = $query->getGrouping();
        $groupComponent->addField('product_id');
        $groupComponent->setFacet(true);
        $groupComponent->setLimit(1);
        
        //add facet for completion
		
        $facetSet = $query->getFacetSet();
        $facetField = $facetSet->createFacetField('text'); 
        $facetField->setField('text');
        $facetField->setMincount(1);
        $facetField->setLimit( $this->getConf('results/autocomplete_suggestions') );
        $facetField->setPrefix($queryString);
		
        $solariumResult = $this->_client->select($query);
        
        if ( $solariumResult ) {
           return $solariumResult->getFacetSet()->getFacet('text');
        } else {
            return null;
        }
    }

    public function getAutoSuggestions( $storeId, $queryString ) {
    	//create basic query with wildcard
    	$query = $this->_client->createSelect();
    	//$query->setDocumentClass('text');
    	$query->setFields('text');
    	$query->setQuery( $queryString . '*' );
    	$query->setRows(0);
    
    	if ( is_numeric( $storeId ) ) {
    		$query->createFilterQuery( 'store_id' )->setQuery( 'store_id:' . intval( $storeId ) );
    	}
    	$solariumResult1 = $this->_client->select($query);
    	$groupComponent = $query->getGrouping();
    	
    	$groupComponent->addField('product_id');
    	//$groupComponent->addField('product_name');
    	$groupComponent->setFacet(false);
    	$groupComponent->setLimit(1);
    	
    	//add facet for completion
    	//$edismaxComponent = $query->getEDisMax();
    	
    	
    	 
    	 //print_r($solariumResult1);
    	 //print_r($solariumResult1->getData());
    	 //exit;
    	 //print_r($query->getResultClass());
    	 //$edisMax = $query->getEDisMax();
    	 //$edisField = $edisMax->setUserFields('*');
    	 
    	 $facetSet = $query->getFacetSet();
    	 $facetField = $facetSet->createFacetField('text');
    	 $facetField->setField('text');
    	 $facetField->setMincount(1);
    	 $facetField->setLimit( $this->getConf('results/autocomplete_suggestions') );
    	 $facetField->setPrefix($queryString);
    	 $solariumResult = $this->_client->select($query);
    	
    	if ( $solariumResult ) {
    		return $solariumResult->getFacetSet()->getFacet('text');
    	} else {
    		return null;
    	}
    	
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
    
    public function getAutoSuggestionsNestIncubate_($storeId = 1, $queryString = "q=*%3A*&start=0&rows=3&wt=json"){
    	$configForSolarium = $this->_getSolariumClientConfig();
    	
    	$solrHost = $configForSolarium['endpoint']['default']['host'];
    	$solrPort = $configForSolarium['endpoint']['default']['port'];
    	$solrPath = $configForSolarium['endpoint']['default']['path'];
    	$solrCore = $configForSolarium['endpoint']['default']['core'];
    	
    	$solrUrl = "http://".$solrHost.":".$solrPort.$solrPath."/".$solrCore."/select?".$queryString;
    	$contents = file_get_contents($solrUrl);
    	
    	$parse_output = json_decode($contents,true);
    	
    	$result = array();
    	$result['docs'] = $parse_output['response']['docs'];
    	$result['totalNumResults'] = $parse_output['response']['numFound'];
    	
    	return $result;
    	
    }
    
    public function getAutoSuggestionsNestIncubate($storeId = 1, $queryString = null){
    	if ( !$this->_working ) {
    		return false;
    	}
    	$result = false;
    	try {
    		$query = $this->_client->createSelect();
    		// Default field, needed when it is not specified in solrconfig.xml
    		$query->addParam( 'df', 'text' );
    		//$query->addParam( 'fq', 'is_featured:1');
    		$query->setQuery( $this->_filterString( "*".$queryString."*" ) );
    		$query->setRows( $this->getConf( 'results/autocomplete_suggestions' ) );
    		$query->setFields( array( 'product_id', 'score', 'product_name', 'category_name') );
    		if ( is_numeric( $storeId ) ) {
    			$query->createFilterQuery( 'store_id' )->setQuery( 'store_id:' . intval( $storeId ) );
    		}
    		
    		$search_category_id = Mage::app()->getRequest()->getParam('cat', false);
    		
    		if($search_category_id = Mage::app()->getRequest()->getParam('cat', false)){
    			$query->createFilterQuery( 'category_id' )->setQuery( 'category_id:' . intval( $search_category_id ) );
    		}
    		
    		$query->addSort( 'is_featured', $query::SORT_DESC );
    		$query->addSort( 'score', $query::SORT_DESC );
    		
    		$query->setTimeAllowed( intval( $this->getConf( 'server/search_timeout' ) ) );
    		$solrResultSet        = $this->_client->select( $query );
    		
    		$this->_lastQueryTime = $solrResultSet->getQueryTime();
    		$result               = array();
    		foreach ( $solrResultSet as $key => $solrResult ) {
    			$result['results'][] = array( 'relevance' => $solrResult[ 'score' ],
    								'product_id' => $solrResult[ 'product_id' ],
    								'product_name' => $solrResult[ 'product_name' ],
    								'product_category_name'	=> $solrResult[	'category_name' ]
    			);
    		}

    		$result['totalNumResults'] = $solrResultSet->getNumFound();    		
    	} catch ( Exception $e ) {
    		$this->_lastError = $e;
    		Mage::log( sprintf( '%s->%s: %s', __CLASS__, __FUNCTION__, $e->getMessage() ), Zend_Log::ERR );
    	}
    	return $result;
    	 
    }
    
    public function getAutoSuggestionsShoppingList($storeId = 1, $queryString = null){
    	if ( !$this->_working ) {
    		return false;
    	}
    	$result = false;
    	try {
    		$query = $this->_client->createSelect();
    		// Default field, needed when it is not specified in solrconfig.xml
    		$query->addParam( 'df', 'text' );
    		
    		//$query->addParam( 'fq', 'product_name:'.$queryString.'*');
    		//$query->setQuery( $this->_filterString( $queryString ) );
    		$query->setRows( 1000 );
    		$query->setFields( array( 'product_id', 'product_name') );
    		if ( is_numeric( $storeId ) ) {
    			$query->createFilterQuery( 'store_id' )->setQuery( 'store_id:' . intval( $storeId ) );
    			$query->createFilterQuery( 'product_name' )->setQuery( 'product_name:*' . $this->_filterString($queryString ).'*');
    		}
    		$query->addSort( 'score', $query::SORT_DESC );
    
    		$query->setTimeAllowed( intval( $this->getConf( 'server/search_timeout' ) ) );
    		$solrResultSet        = $this->_client->select( $query );
    
    		$this->_lastQueryTime = $solrResultSet->getQueryTime();
    		$result               = array();
    		foreach ( $solrResultSet as $key => $solrResult ) {
    			$result[] = array(
    					'product_id' => $solrResult[ 'product_id' ],
    					'product_name' => $solrResult[ 'product_name' ],
    			);
    		}
    
    	} catch ( Exception $e ) {
    		$this->_lastError = $e;
    		Mage::log( sprintf( '%s->%s: %s', __CLASS__, __FUNCTION__, $e->getMessage() ), Zend_Log::ERR );
    	}
    	
    	return $result;
    
    }    
    

}