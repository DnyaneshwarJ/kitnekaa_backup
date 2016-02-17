<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grid
 *
 * @author root
 */
class Amar_Profile_Block_Adminhtml_Customer_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('attribute_filter');

    }

    

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('profile/customer_attribute')->getCollection();
        $collection->addFilterToMap('id', 'main_table.attribute_id', 'fields');
        $this->setCollection($collection);
        parent::_prepareCollection();
        
        return $this;
    }

    

    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> Mage::helper('profile')->__('Attribute ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
        ));
        
        $this->addColumn('frontend_label',
            array(
                'header'=> Mage::helper('profile')->__('Attribute Label'),
                'index' => 'frontend_label',
        ));

        

        $this->addColumn('attribute_code', array(
            'header'    => Mage::helper('profile')->__('Attribute Code'),
            'width'     => '150px',
            'align'     =>'left',
            'index' => 'attribute_code',
        ));
        
        $this->addColumn('frontend_input', array(
            'header'    => Mage::helper('profile')->__('Frontend Control'),
            'width'     => '150px',
            'align'     =>'left',
            'index' => 'frontend_input',
        ));

        
        $this->addColumn('is_unique', array(
            'header'    => Mage::helper('profile')->__('Is Unique ?'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_unique',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            )
        ));
        
        
        $this->addColumn('is_visible', array(
            'header'    => Mage::helper('profile')->__('Is Visible ?'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_visible',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            )
        ));
        
        $this->addColumn('is_system', array(
            'header'    => Mage::helper('profile')->__('Is System Defined ?'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_system',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            )
        ));
        
        $this->addColumn('sort_order',
            array(
                'header'=> Mage::helper('profile')->__('Sort Order'),
                'index' => 'sort_order',
                'width'     => '80px',
        ));
        
        $this->addColumn('is_required', array(
            'header'    => Mage::helper('profile')->__('Is Required ?'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_required',
            'type'      => 'options',
            'options'   => array(
                1 => 'Yes',
                0 => 'No',
            )
        ));

        
        

        return parent::_prepareColumns();
    }

    
    

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'code'=>$row->getAttributeCode())
        );
    }
}

?>
