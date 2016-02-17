<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 00:12
 */
class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /*
     * Init grid default properties
     * */
    public function __construct()
    {
        parent::__construct();
        $this->setId('cameraslide_list_grid');
        $this->setDefaultSort('slide_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /*
     * Retrieve collection class
     *
     * @return string
     * */
    protected function _getCollectionClass()
    {
        return 'sm_cameraslide/slide';
    }

    /*
        Prepare collection for grid

        @return Sm_Cameraslide_Block_Adminhtml_Cameraslide_Grid
    */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel($this->_getCollectionClass())->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /*
     * Create the field for grid
     * */
    public function _prepareColumns()
    {
        $this->addColumn('slide_id', array(
            'header' => Mage::helper('sm_cameraslide')->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'slide_id',
        ));

        $this->addColumn('name_slide', array(
            'header' => Mage::helper('sm_cameraslide')->__('Name Slide'),
            'align'  => 'left',
            'width'  => '80%',
            'index'  => 'name_slide',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sm_cameraslide')->__('Status'),
            'align'  => 'center',
            'index'  => 'status',
            'type'   => 'options',
            'width'  => '100px',
            'options' => Mage::getModel($this->_getCollectionClass())->getOptionStatus(),
        ));

        $this->addColumn('preview', array(
            'header'    => Mage::helper('sm_cameraslide')->__('Preview'),
            'type'      => 'action',
            'align'     => 'center',
            'getter'    => 'getId',
            'width'     => '100px',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('sm_cameraslide')->__('Preview'),
                    'field'     => 'id',
                    'target'    => 'blank',
                    'url'       => array('base' => 'cameraslide/index/preview')
                )
            ),
            'filter'    => false,
            'sortable'  => false
        ));

        $this->addColumn('action', array(
            'header'	=> Mage::helper('sm_cameraslide')->__('Action'),
            'width'		=> '100px',
            'align'     => 'center',
            'type'		=> 'action',
            'getter'	=> 'getId',
            'actions'	=> array(array(
                'caption' 	=> Mage::helper('sm_cameraslide')->__('Edit'),
                'url'		=> array('base' => '*/*/edit'),
                'field'		=> 'id',
                'class'     => 'scalable'
            )),
            'filter' 	=> false,
            'sortable'	=> false,
            'index'     => 'stores',
            'is_system' => true,
            'class' => 'scalable'
        ));
        return parent::_prepareColumns();
    }

    /**
     * Prepare and set options for massaction
     *
     * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slide_id');
        $this->getMassactionBlock()->setFormFieldName('slide_id');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('sm_cameraslide')->__('Delete'),
            'url'  => $this->getUrl('*/*/delete'),
            'confirm' => Mage::helper('sm_cameraslide')->__('Are you sure?'),
        ));

        return $this;
    }

    /*
        return row url for js event handles

        @return string
    */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl( '*/*/grid', array(
            '_current' => true
        ) );
    }
}