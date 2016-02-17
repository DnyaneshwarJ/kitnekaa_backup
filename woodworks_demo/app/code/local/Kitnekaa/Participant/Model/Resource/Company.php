<?php
class Kitnekaa_Participant_Model_Resource_Company
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        /**
         * Tell Magento the database name and primary key field to persist 
         * data to. Similar to the _construct() of our Model, Magento finds 
         * this data from config.xml by finding the <resourceModel/> node 
         * and locating children of <entities/>.
         * 
         * In this example:
         * - kitnekaa_participant is the Model alias
         * - brand is the entity referenced in config.xml
         * - entity_id is the name of the primary key column
         * 
         * As a result Magento will write data to the table 
         * 'kitnekaa_participant_brand' and any calls to 
         * $model->getId() will retrieve the data from the column 
         * named 'entity_id'.
         */
        $this->_init('kitnekaa_participant/company', 'entity_id');
    }
    public function getCompanies()
    {
    	$read      = $this->_getReadAdapter();
    	$select    = $read->select()
    	->from(
    			array('cpe' => $this->getTable('kitnekaa_participant/company')));
    		$companies= $read->fetchAll($select);
    		return $companies;
    }
}