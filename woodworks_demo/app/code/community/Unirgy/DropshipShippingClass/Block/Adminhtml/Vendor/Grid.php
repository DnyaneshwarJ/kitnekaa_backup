<?php

class Unirgy_DropshipShippingClass_Block_Adminhtml_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('udshipclassVendorGrid');
        $this->setDefaultSort('class_name');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udshipclass/vendor')
            ->getCollection()
            ->setFlag('load_region_labels', true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('class_name',
            array(
                'header'    => Mage::helper('udropship')->__('Class Name'),
                'align'     => 'left',
                'index'     => 'class_name'
            )
        );

        $this->addColumn('country_id', array(
            'header'        => Mage::helper('udropship')->__('Country'),
            'type'          => 'text',
            'align'         => 'left',
            'index'         => 'country_id',
            'renderer'      => 'udshipclass/adminhtml_gridRenderer_countries',
            'filter'        => false,
            'sortable'      => false
        ));

        $this->addColumn('region_name', array(
            'header'        => Mage::helper('udropship')->__('State/Region'),
            'header_export' => Mage::helper('udropship')->__('State'),
            'align'         => 'left',
            'index'         => 'region_name',
            'type'          => 'text',
            'renderer'      => 'udshipclass/adminhtml_gridRenderer_regions',
            'filter'        => false,
            'sortable'      => false,
            'nl2br'         => true,
            'default'       => '*',
        ));

        $this->addColumn('postcode', array(
            'header'        => Mage::helper('udropship')->__('Zip/Post Code'),
            'align'         => 'left',
            'index'         => 'postcode',
            'type'          => 'text',
            'renderer'      => 'udshipclass/adminhtml_gridRenderer_postcodes',
            'filter'        => false,
            'sortable'      => false,
            'nl2br'         => true,
            'default'       => '*',
        ));

        $this->addColumn('sort_order', array(
            'header'        => Mage::helper('udropship')->__('Sort Order'),
            'align'         =>'left',
            'index'         => 'sort_order',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
