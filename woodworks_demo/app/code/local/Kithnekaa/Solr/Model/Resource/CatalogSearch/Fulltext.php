<?php

class Kithnekaa_Solr_Model_Resource_CatalogSearch_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{
	
	public function cleanIndex( $storeId = null, $productIds = null ) {
		parent::cleanIndex( $storeId, $productIds );
		/**
		 * If it is enabled for one store, clean for the current store.
		 * This is needed to clean up when you switch Solarium Search from enable to disable for a store.
		*/
		if ( JeroenVermeulen_Solarium_Model_Engine::isEnabled() ) {
			Mage::getSingleton( 'jeroenvermeulen_solarium/engine' )->cleanIndex( $storeId, $productIds );
		}
		return $this;
	}
	
    public function rebuildIndex( $storeId = null, $productIds = null ) {
        parent::rebuildIndex( $storeId, $productIds );
        if ( Kithnekaa_Solr_Model_Engine::isEnabled( $storeId ) ) {
            $helper       = Mage::helper( 'jeroenvermeulen_solarium' );
            $engine       = Mage::getSingleton( 'kithnekaa_solr/engine' );
            $startTime    = microtime( true );
            $ok           = $engine->rebuildIndex( $storeId, $productIds );
            $timeUsed     = microtime( true ) - $startTime;
            // When product IDs are supplied, it is an automatic update, and we should not show messages.
            if ( null == $productIds ) {
                if ( $ok ) {
                    $message = $helper->__( 'Solr Index was rebuilt in %s seconds.', sprintf( '%.02f', $timeUsed ) );
                    if ( $engine->isShellScript() ) {
                        echo $message . "\n";
                    } else {
                        Mage::getSingleton( 'adminhtml/session' )->addSuccess( $message );
                    }
                } else {
                    $message = $helper->__( 'Error reindexing Solr: %s', $engine->getLastError() );
                    if ( $engine->isShellScript() ) {
                        echo $message . "\n";
                    } else {
                        Mage::getSingleton( 'adminhtml/session' )->addError( $message );
                    }
                }
            }
        }
        return $this;
    }
    
    public function prepareResult( $object, $queryText, $query ) {
    	if ( !$query->getIsProcessed() ) {
    		if ( JeroenVermeulen_Solarium_Model_Engine::isEnabled( $query->getStoreId() ) ) {
    			$adapter           = $this->_getWriteAdapter();
    			$searchResultTable = $this->getTable( 'catalogsearch/result' );
    			$engine            = Mage::getSingleton( 'kithnekaa_solr/engine' );
    			if ( $engine->isWorking() ) {
    				$searchResult = $engine->query( $query->getStoreId(), "*".$queryText."*" );
    				//echo "<pre>";print_r($searchResult);exit;
    				if ( false !== $searchResult ) {
    					if ( 0 == count($searchResult) ) {
    						// No results, we need to check if the index is empty.
    						if ( $engine->isEmpty( $query->getStoreId() ) ) {
    							Mage::Log( sprintf('%s - Warning: index is empty', __CLASS__), Zend_Log::WARN );
    						} else {
    							$query->setIsProcessed( 1 );
    						}
    					} else {
    						//
    						foreach ( $searchResult as $data ) {
    							$data[ 'query_id' ] = $query->getId();
    							$adapter->insertOnDuplicate( $searchResultTable, $data, array( 'relevance' ) );
    						}
    						$query->setIsProcessed( 1 );
    					}
    				}
    			}
    		}
    		if ( !$query->getIsProcessed() ) {
    			Mage::log( 'Solr disabled or something went wrong, fallback to Magento Fulltext Search', Zend_Log::WARN );
    			return parent::prepareResult( $object, $queryText, $query );
    		}
    	}
    	return $this;
    }

}