<?php
    class Sm_Cameraslide_Block_Adminhtml_Cameraslide_Edit_Tab_Sliders extends Mage_Adminhtml_Block_Widget_Grid
    {
        protected $_slide = null;
        /*
         * Init grid default properties
         * */
        public function __construct()
        {
            parent::__construct();
            $this->setId('sliders_list_grid');
            $this->setDefaultSort('priority');
            $this->setDefaultDir('DESC');
            $this->setSaveParametersInSession(true);
            $this->setUseAjax(true);

            /*
             * Để làm mất riêng 2 button "Reset Filter" and "Search"
             *
             * ta sử dụng $this->setFilterVisibility(false);
             **/
            $this->setFilterVisibility(false);
        }

        protected function _prepareLayout()
        {
            $this->getLayout()->getBlock('head')->addJs('sm/cameraslide/js/renderhelper.js');
            $sliders = $this->getSliders();
            if($sliders && $sliders->getId())
            {
                $url = $this->getUrl('*/*/addSliders', array(
                    'sid'   => $sliders->getId()
                ));
                $this->setChild('addSlidesButton', $this->getLayout()->createBlock( 'adminhtml/widget_button' )->setData( array(
                    'label' => Mage::helper( 'sm_cameraslide' )->__( 'Add Slide' ),
                    'onclick' => "setLocation('$url')",
                    'class' => 'scale add',
                    'id' => 'add_slides'
                ) ) );
            }
            return parent::_prepareLayout();
        }

        /*
        * Retrieve collection class
        *
        * @return string
        * */
        protected function _getCollectionSlideClass()
        {
            return 'sm_cameraslide/slide';
        }

        /*
        * Retrieve collection class
        *
        * @return string
        * */
        protected function _getCollectionSlidersClass()
        {
            return 'sm_cameraslide/sliders';
        }

        /*
         * Get Id of slide
         * */
        protected function getSliders()
        {
            if(!$this->_slide)
            {
                $modelSlide = Mage::getModel($this->_getCollectionSlideClass());
                $id = $this->getRequest()->getParam('id', null);
                if(is_numeric($id))
                {
                    $modelSlide->load($id);
                }
                $this->_slide = $modelSlide;
            }
            return $this->_slide;
        }



        /*
         * Prepare and set collection of grid
         * */
        protected function _prepareCollection()
        {
            $sliders = $this->getSliders();

            $collection = Mage::getModel($this->_getCollectionSlidersClass())->getCollection()->addSlideFilter($sliders && $sliders->getId() ? $sliders : 0);
//            $collection = Mage::getModel($this->_getCollectionSlidersClass())->getCollection()->addFieldToFilter('slide_id', $this->getIdSliders());
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }


        /*
         * Prepare and add columns to grid
         *
         * @return Mage_Adminhtml_Block_Widget_Grid
         * */
        public function _prepareColumns()
        {
            $this->addColumn('sliders_id', array(
                'name'      => 'sliders_id',
                'header'    => Mage::helper('sm_cameraslide')->__('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'sliders_id'
            ));

            $this->addColumn('sliders_title', array(
                'header'    => Mage::helper('sm_cameraslide')->__('Title'),
                'align'     => 'center',
                'width'     => '40%',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'sm_cameraslide/adminhtml_widget_grid_column_renderer_sliders_title'
            ));

            $this->addColumn('sliders_thumb', array(
                'header'    => Mage::helper('sm_cameraslide')->__('Thumb'),
                'align'     => 'center',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'sm_cameraslide/adminhtml_widget_grid_column_renderer_sliders_thumb'
            ));

            $this->addColumn('sliders_priority', array(
                'header'    => Mage::helper('sm_cameraslide')->__('Priority'),
                'align'     => 'center',
                'width'     => '160',
                'renderer'  => 'sm_cameraslide/adminhtml_widget_grid_column_renderer_sliders_priority'
            ));

            $this->addColumn('action', array(
                'header'	=> Mage::helper('sm_cameraslide')->__('Action'),
//                'width'		=> '100px',
                'align'     => 'center',
                'type'		=> 'action',
                'getter'	=> 'getId',
                'actions'	=> array(
                    array(
                        'field'		=> 'id',
                        'class'     => 'scalable',
                        'caption' 	=> Mage::helper('sm_cameraslide')->__('Edit'),
                        'url'		=> array(
                            'base'      => '*/*/addSliders',
                            'params'    => array(
                                'sid'   => $this->_slide->getId()
                            )
                        ),
                    ),
                ),
                'filter' 	=> false,
                'sortable'	=> false,
                'is_system' => true,
            ));

            $this->addColumn('delete', array(
                'header'	=> Mage::helper('sm_cameraslide')->__('Delete'),
//                'width'		=> '100px',
                'align'     => 'center',
                'type'		=> 'action',
                'getter'	=> 'getId',
                'actions'	=> array(
                    array(
                        'field'		=> 'id',
                        'caption' 	=> Mage::helper('sm_cameraslide')->__('Delete'),
                        'confirm'   => Mage::helper('sm_cameraslide')->__('Are you sure?'),
                        'url'		=> array(
                            'base'      => '*/*/deleteSliders',
                            'params'    => array(
                                'sid'   => $this->_slide->getId(),
                                'activeTab' => 'form_slide'
                            )
                        ),
                    ),
                ),
                'filter' 	=> false,
                'sortable'	=> false,
                'is_system' => true,
            ));
            return parent::_prepareColumns();
        }

        /**
         * Prepare and set options for massaction
         *
         * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
         */
//        protected function _prepareMassaction()
//        {
//            $this->setMassactionIdField('sliders_id');
//            $this->getMassactionBlock()->setFormFieldName('sliders_id');
//            $this->getMassactionBlock()->setUseSelectAll(true);
//
//            $this->getMassactionBlock()->addItem('delete', array(
//                'label'=> Mage::helper('sm_cameraslide')->__('Delete'),
//                'url'  => $this->getUrl('*/*/deleteSliders'),
//                'confirm' => Mage::helper('sm_cameraslide')->__('Are you sure?'),
//            ));
//
//            return $this;
//        }

        /*
            Return row url for js event handles

            @return string
        */
        public function getRowUrl($row)
        {
//            return $this->getUrl('*/*/addSliders', array(
//                'sid' => $this->getIdSlide(),
//                'id' => $row->getId()
//                )
//            );
//            return '';
        }

        public function getGridUrl()
        {
            return $this->getUrl( '*/*/gridSliders', array(
                '_current' => true
            ) );
        }

        public function getMainButtonsHtml()
        {
            $buttons = $this->getChildHtml('addSlidesButton');
            return $buttons;
//            if($this->getSliders())
//            {
//                $html = parent::getMainButtonsHtml();
//                $url = $this->getUrl('*/*/addSliders', array(
//                    'sid'   => $this->getSliders()
//                ));
//                $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
//                    ->setData(array(
//                        'label'     => Mage::helper('adminhtml')->__('Add Sliders'),
//                        'onclick'   => "setLocation('$url')",
//                        'class'     => 'addsliders'
//                    ))->toHtml();
//                return $addButton.$html;
//            }
        }
    }
?>