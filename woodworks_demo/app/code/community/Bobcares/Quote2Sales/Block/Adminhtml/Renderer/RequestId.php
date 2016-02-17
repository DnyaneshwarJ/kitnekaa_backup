<?php

/**
 * Class includes all functions for displaying the request id link of the request
 * @created on 12th Jun 2015
 * @category Renderer class
 * @author Bobcares
 *
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Renderer_RequestId extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * @desc This function is used for displaying the request id link in admin panel grid
     * @param Varien_Object $row : input data (request id)
     * @return string : The request link corresponding quote.
     */
    public function render(Varien_Object $row) {
        $requestId = $row->getData('request_id');

        //If there is a request_id 
        if ($requestId) {
            return "<a href='" . Mage::helper('adminhtml')->getUrl("quote2sales/adminhtml_request/view", array('id' => $requestId)) . "' style = 'text-decoration: none !important;'>$requestId</a>" . "</br>";
        } else {
            return "";
        }
    }

}
