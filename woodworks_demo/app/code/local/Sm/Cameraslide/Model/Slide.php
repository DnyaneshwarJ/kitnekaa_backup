<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 24/01/2015
 * Time: 09:17
 */
class Sm_Cameraslide_Model_Slide extends Mage_Core_Model_Abstract
{
    // Status Enable or Disable
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 2;

    // Type '%' or 'px'
    const PX = 'px';
    const PERCENT = '%';

    // Status Yes or No
    const YES = 1;
    const NO = 2;

    // Options True | False
    const TRUE  = 'true';
    const FALSE = 'false';

    // Options Easing
    const EASING_1      = 'linear';
    const EASING_2      = 'swing';
    const EASING_3      = 'easeInQuad';
    const EASING_4      = 'easeOutQuad';
    const EASING_5      = 'easeInOutQuad';
    const EASING_6      = 'easeInCubic';
    const EASING_7      = 'easeOutCubic';
    const EASING_8      = 'easeInOutCubic';
    const EASING_9      = 'easeInQuart';
    const EASING_10     = 'easeOutQuart';
    const EASING_11     = 'easeInOutQuart';
    const EASING_12     = 'easeInQuint';
    const EASING_13     = 'easeOutQuint';
    const EASING_14     = 'easeInOutQuint';
    const EASING_15     = 'easeInExpo';
    const EASING_16     = 'easeOutExpo';
    const EASING_17     = 'easeInOutExpo';
    const EASING_18     = 'easeInSine';
    const EASING_19     = 'easeOutSine';
    const EASING_20     = 'easeInOutSine';
    const EASING_21     = 'easeInCirc';
    const EASING_22     = 'easeOutCirc';
    const EASING_23     = 'easeInOutCirc';
    const EASING_24     = 'easeInElastic';
    const EASING_25     = 'easeOutElastic';
    const EASING_26     = 'easeInOutElastic';
    const EASING_27     = 'easeInBack';
    const EASING_28     = 'easeOutBack';
    const EASING_29     = 'easeInOutBack';
    const EASING_30     = 'easeInBounce';
    const EASING_31     = 'easeOutBounce';
    const EASING_32     = 'easeInOutBounce';

    /*
     * Define resource model
     * */
    protected function _construct(){
        $this->_init('sm_cameraslide/slide');
    }

    /*
     * Get Status
     * */
    public function getOptionStatus()
    {
        $option = new Varien_Object(array(
            self::STATUS_ENABLED => Mage::helper('sm_cameraslide')->__('Enable'),
            self::STATUS_DISABLED => Mage::helper('sm_cameraslide')->__('Disable'),
        ));
        return $option->getData();
    }

    /*
     * Get Type
     * */
    public function getOptionType()
    {
        $option = new Varien_Object(array(
            self::PX => Mage::helper('sm_cameraslide')->__('px'),
            self::PERCENT => Mage::helper('sm_cameraslide')->__('%'),
        ));
        return $option->getData();
    }

    /*
     * Get status Yes or No
     * */
    public function getOptionYesNo()
    {
        $option = new Varien_Object(array(
            self::YES => Mage::helper('sm_cameraslide')->__('Yes'),
            self::NO => Mage::helper('sm_cameraslide')->__('No'),
        ));
        return $option->getData();
    }

    // get Options True | False
    public function getTrueFalse()
    {
        $options = new Varien_Object(array(
            self::TRUE  => Mage::helper('sm_cameraslide')->__('True'),
            self::FALSE => Mage::helper('sm_cameraslide')->__('False'),
        ));
        return $options->getData();
    }

    // Get Options Easing
    public function getEasing()
    {
        return array(
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Linear'),
                'value' => array(
                    array(
                        'value' => self::EASING_1,
                        'label' => Mage::helper('sm_cameraslide')->__('Linear')
                    )
                )
            ),
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Swing'),
                'value' => array(
                    array(
                        'value' => self::EASING_2,
                        'label' => Mage::helper('sm_cameraslide')->__('Swing')
                    )
                )
            ),
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Quad'),
                'value' => array(
                    array(
                        'value' => self::EASING_3,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quad')
                    ),
                    array(
                        'value' => self::EASING_4,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quad')
                    ),
                    array(
                        'value' => self::EASING_5,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quad')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Cubic'),
                'value' => array(
                    array(
                        'value' => self::EASING_6,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Cubic')
                    ),
                    array(
                        'value' => self::EASING_7,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Cubic')
                    ),
                    array(
                        'value' => self::EASING_8,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Cubic')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Quart'),
                'value' => array(
                    array(
                        'value' => self::EASING_9,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quart')
                    ),
                    array(
                        'value' => self::EASING_10,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quart')
                    ),
                    array(
                        'value' => self::EASING_11,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quart')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Quint'),
                'value' => array(
                    array(
                        'value' => self::EASING_12,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quint')
                    ),
                    array(
                        'value' => self::EASING_13,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quint')
                    ),
                    array(
                        'value' => self::EASING_14,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quint')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Expo'),
                'value' => array(
                    array(
                        'value' => self::EASING_15,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Expo')
                    ),
                    array(
                        'value' => self::EASING_16,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Expo')
                    ),
                    array(
                        'value' => self::EASING_17,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Expo')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Sine'),
                'value' => array(
                    array(
                        'value' => self::EASING_18,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Sine')
                    ),
                    array(
                        'value' => self::EASING_19,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Sine')
                    ),
                    array(
                        'value' => self::EASING_20,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Sine')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Circ'),
                'value' => array(
                    array(
                        'value' => self::EASING_21,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Circ')
                    ),
                    array(
                        'value' => self::EASING_22,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Circ')
                    ),
                    array(
                        'value' => self::EASING_23,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Circ')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Elastic'),
                'value' => array(
                    array(
                        'value' => self::EASING_24,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Elastic')
                    ),
                    array(
                        'value' => self::EASING_25,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Elastic')
                    ),
                    array(
                        'value' => self::EASING_26,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Elastic')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Back'),
                'value' => array(
                    array(
                        'value' => self::EASING_27,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Back')
                    ),
                    array(
                        'value' => self::EASING_28,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Back')
                    ),
                    array(
                        'value' => self::EASING_29,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Back')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Bounce'),
                'value' => array(
                    array(
                        'value' => self::EASING_30,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Bounce')
                    ),
                    array(
                        'value' => self::EASING_31,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Bounce')
                    ),
                    array(
                        'value' => self::EASING_32,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Bounce')
                    )
                )
            ),
        );
    }

    // Get Options Easing For Only Mobile
    public function getMobileEasing()
    {
        return array(
            array(
                'value' => '',
                'label' => Mage::helper('sm_cameraslide')->__()
            ),
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Linear'),
                'value' => array(
                    array(
                        'value' => self::EASING_1,
                        'label' => Mage::helper('sm_cameraslide')->__('Linear')
                    )
                )
            ),
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Swing'),
                'value' => array(
                    array(
                        'value' => self::EASING_2,
                        'label' => Mage::helper('sm_cameraslide')->__('Swing')
                    )
                )
            ),
            array(
                'label' => Mage::helper('sm_cameraslide')->__('Quad'),
                'value' => array(
                    array(
                        'value' => self::EASING_3,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quad')
                    ),
                    array(
                        'value' => self::EASING_4,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quad')
                    ),
                    array(
                        'value' => self::EASING_5,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quad')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Cubic'),
                'value' => array(
                    array(
                        'value' => self::EASING_6,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Cubic')
                    ),
                    array(
                        'value' => self::EASING_7,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Cubic')
                    ),
                    array(
                        'value' => self::EASING_8,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Cubic')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Quart'),
                'value' => array(
                    array(
                        'value' => self::EASING_9,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quart')
                    ),
                    array(
                        'value' => self::EASING_10,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quart')
                    ),
                    array(
                        'value' => self::EASING_11,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quart')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Quint'),
                'value' => array(
                    array(
                        'value' => self::EASING_12,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Quint')
                    ),
                    array(
                        'value' => self::EASING_13,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Quint')
                    ),
                    array(
                        'value' => self::EASING_14,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Quint')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Expo'),
                'value' => array(
                    array(
                        'value' => self::EASING_15,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Expo')
                    ),
                    array(
                        'value' => self::EASING_16,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Expo')
                    ),
                    array(
                        'value' => self::EASING_17,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Expo')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Sine'),
                'value' => array(
                    array(
                        'value' => self::EASING_18,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Sine')
                    ),
                    array(
                        'value' => self::EASING_19,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Sine')
                    ),
                    array(
                        'value' => self::EASING_20,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Sine')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Circ'),
                'value' => array(
                    array(
                        'value' => self::EASING_21,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Circ')
                    ),
                    array(
                        'value' => self::EASING_22,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Circ')
                    ),
                    array(
                        'value' => self::EASING_23,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Circ')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Elastic'),
                'value' => array(
                    array(
                        'value' => self::EASING_24,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Elastic')
                    ),
                    array(
                        'value' => self::EASING_25,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Elastic')
                    ),
                    array(
                        'value' => self::EASING_26,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Elastic')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Back'),
                'value' => array(
                    array(
                        'value' => self::EASING_27,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Back')
                    ),
                    array(
                        'value' => self::EASING_28,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Back')
                    ),
                    array(
                        'value' => self::EASING_29,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Back')
                    )
                )
            ),array(
                'label' => Mage::helper('sm_cameraslide')->__('Bounce'),
                'value' => array(
                    array(
                        'value' => self::EASING_30,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Bounce')
                    ),
                    array(
                        'value' => self::EASING_31,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease Out Bounce')
                    ),
                    array(
                        'value' => self::EASING_32,
                        'label' => Mage::helper('sm_cameraslide')->__('Ease In Out Bounce')
                    )
                )
            ),
        );
    }


    // Get Options Loader width Opacity
    public function getLoaderOpacity()
    {
        $array = array();
        for((int) $i=0; $i<=10; $i++)
        {
            $array[] = $i/10;
        }
        $array2 = array();
        foreach($array as $a)
        {
            $array2[] = array(
                'value' => $a,
                'label' => Mage::helper('sm_cameraslide')->__("$a")
            );
        }
        $options = new Varien_Object($array2);
        return $options->getData();
    }

    public function _afterLoad()
    {
        $id = $this->getId();
        $this->setData( (array) Mage::helper( 'core' )->jsonDecode( $this->getParams() ) );
        $this->setId( $id );
        $this->setSlideid( $id );
        return parent::_afterLoad();
    }

    public function _beforeSave()
    {
        if ( is_array( $this->getData( 'params' ) ) )
        {
            $this->setData( 'params', Mage::helper( 'core' )->jsonEncode( $this->getParams() ) );
        }
        return parent::_beforeSave();
    }

    /*
     *
     * */
    public function getAllSliders($status = false)
    {
        $collection = Mage::getModel('sm_cameraslide/sliders')->getCollection()->addSlideFilter($this)->setOrder('priority', 'asc');
        $sliders = array();
        foreach($collection as $c)
        {
            $id = $c->getId();
            $layers = $c->getLayers();
            $c->setData(Mage::helper('core')->jsonDecode($c->getParams()));
            $c->setLayers(Mage::helper('core')->jsonDecode($layers));
            $c->setId($id);
            if($status)
            {
                if($c->getData('status') == '1')
                {
                    $sliders[] = $c;
                }
            }else{
                $sliders[]  = $c;
            }
        }
        return $sliders;
    }
}