<?php
class Kitnekaa_Shoppinglist_Block_Adminhtml_Editlist_Renderer_Edit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
		public function render(Varien_Object $row)
		{
			$customer_id = $this->getRequest()->getParam('customer_id');
			$list_id = $this->getRequest()->getParam('list_id');
			$product_id = $row->getId();
			$value =  $row->getProductId();
			$form_key = Mage::getSingleton('core/session')->getFormKey(); 
			
			$ab = Mage::helper("adminhtml")->getUrl("*/shoppinglist/shownonexistform", array('customer_id' => $customer_id, 'list_id'=>$list_id, 'product_id' => $product_id, 'form_key' => $form_key));

		    if(!isset($value))
		    {
		   			$a = "<a href=".$ab." style='color:red;'>Edit</a>";
		    }else
		    {
				  	$a = "";
		    }

			return $a;

		}

}