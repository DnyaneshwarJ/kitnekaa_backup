<?php

class Kithnekaa_Solr_Model_CatalogSearch_Indexer_Fulltext extends Mage_CatalogSearch_Model_Indexer_Fulltext
{

    /**
     * @return string
     */
    public function getDescription() {
        $result = parent::getDescription();
        if ( Kithnekaa_Solr_Model_Engine::isEnabled() ) {
            $helper = Mage::helper( 'jeroenvermeulen_solarium' );
            $result .= ' - ' . $helper->__( 'POWERED BY SOLARIUM KITHNEKAA' );
        }
        return $result;
    }

}