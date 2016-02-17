<?php
	class Neo_Adminhtml_Block_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid{

		protected function _prepareCollection()
	    {
			$collection = Mage::getResourceModel('customer/customer_collection')
				->addNameToSelect()
				->addAttributeToSelect('email')
				->addAttributeToSelect('created_at')
				->addAttributeToSelect('group_id')
				->addAttributeToSelect('company_id')
                ->addAttributeToSelect('company_name')
				->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
				->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
				->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
				->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
				->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

			$collection->getSelect()->joinLeft(array('uc' => 'kitnekaa_company'),'e.entity_id = uc.customer_id',array('uc.company_id','uc.company_name','uc.vat_tin_verified','uc.vat_tin_verified_by'));
            $collection->getSelect()->group('e.entity_id');

			$this->setCollection($collection);
			return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
		}

		protected function _prepareColumns()
        {

            $this->addColumnAfter('company_name', array(
                'header' => Mage::helper('customer')->__('Company'),
                'type' => 'options',
                'options'=>$this->_get_companies(),
                'index' => 'company_name',
                'filter_condition_callback' => array($this, '_filter_company'),
            ),'entity_id');

            $this->addColumnAfter('vat_tin_verified', array(
                'header'    => Mage::helper('customer')->__('Vat & Tin No. Verified Status'),
                'type' => 'options',
                'index'     => 'vat_tin_verified',
                'filter_index' => 'uc.vat_tin_verified',
                'gmtoffset' => true,
                'filter_condition_callback' => 'filter_vat_tin_verified',
                'options'   => Mage::getModel('users/company')->getStates()
            ),'billing_region');

            if (!function_exists('filter_vat_tin_verified')) {
                /**
                 * @param Mage_Customer_Model_Resource_Customer_Collection $collection
                 * @param Mage_Adminhtml_Block_Widget_Grid_Column          $column
                 */
                function filter_vat_tin_verified($collection, $column)
                {
                    if (!$column->getFilter()->getCondition()) {
                        return;
                    }

                    $condition = $collection->getConnection()
                        ->prepareSqlCondition('uc.vat_tin_verified', $column->getFilter()->getCondition());
                    $collection->getSelect()->where($condition);
                }
            }

            $this->addColumnAfter('vat_tin_verified_by', array(
                'header' => Mage::helper('customer')->__('Vat & Tin No. Verified Status By'),
                'type' => 'text',
                'index' => 'vat_tin_verified_by',
                'filter_condition_callback' => array($this, '_vatTinVerifiedBy'),
            ),'vat_tin_verified');



            return parent::_prepareColumns();
        }

        protected function _vatTinVerifiedBy($collection, $column)
        {
            if (!$value = $column->getFilter()->getValue()) {
                return $this;
            }
     
            $this->getCollection()->getSelect()->where("uc.vat_tin_verified_by like ?", "%$value%");
            return $this;
        }

        protected function _filter_company($collection, $column)
        {
            if (!$value = $column->getFilter()->getValue()) {
                return $this;
            }

            $this->getCollection()->getSelect()->where("uc.company_name ='".$value."'");
            return $this;
        }

        protected function _get_companies()
        {
            $companies=array();
            $obj = Mage::getModel('users/company')
                ->getCollection();


            foreach($obj as $value)
            {
                $companies[$value->getCompanyName()]=$value->getCompanyName();
            }
            return $companies;
        }
	}
