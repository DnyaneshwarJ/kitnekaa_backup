<?php
/*------------------------------------------------------------------------
 # SM Market - Version 1.0
 # Copyright (c) 2014 The YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Market_Helper_Data extends Mage_Core_Helper_Abstract{

	public function __construct(){
		$this->defaults = array(
			/* general options */
			'layout_styles'				 => '1',
			'color'						 => 'tomato',
			'header_styles'              => '1',
			'body_font_family'			 => 'Arial',
			'body_font_size'			 => '13px',
			'google_font'				 => 'Open Sans',
			'google_font_targets'		 => '',
			'body_link_color'			 => '#666666',
			'body_link_hover_color'		 => '#666666',
			'body_text_color'			 => '#666666',
			'body_title_color'			 => '#444444',
			'title_color_targets'		 => '',
			'body_background_color'		 => '#ffffff',			
			'body_background_image'		 => '',
			'use_customize_image'		 => '',
			'background_customize_image' => '',
			'background_repeat'		     => '',			
			'background_position'		 => '',
			'menu_styles'                => '1',
			'menu_ontop'		         => '1',			
			'responsive_menu'		     => '3',			
			/* detail market */
			'show_imagezoom'		     => '',
			'zoom_mode'		 			 => '',
			'use_addthis' 				 => '',
			'use_customblock' 				 => '',
			'show_related' 				 => '',
			'related_number'		     => '',			
			'show_upsell'		 		 => '',
			'upsell_number'              => '',
			'show_customtab'		     => '',			
			'customtab_name'		     => '',
			'customtab_content'		     => '',	
			/*Rich Snippets*/
			'use_rich_snippet'   		 => '1',
			'set_breadcrumbs'   		 => '1',
			'google_plus_href'   		 => 'https://plus.google.com/u/0/+Smartaddons',			
			/* advanced */
			'show_popup'		     	 => '1',
			'show_cpanel'		     	 => '1',
			'use_ajaxcart'		 		 => '1',
			'show_addtocart' 			 => '1',
			'show_wishlist'		     	 => '1',			
			'show_compare'		 		 => '1',
			'show_quickview'             => '1',
			'custom_copyright'		     => '',			
			'copyright'		     		 => '',
			'custom_css'		     	 => '',	
			'custom_js'		     		 => '',		
			'compress_css_js'		     => '',		
			'enable_yuicompressor'       => '',
		);
	}

	function get($attributes=array()){
		$data           = $this->defaults;
		$general        = Mage::getStoreConfig("market_cfg/general");
		$detail_market = Mage::getStoreConfig("market_cfg/detail_market");
		$social_market = Mage::getStoreConfig("market_cfg/social_market");
		$advanced 	    = Mage::getStoreConfig("market_cfg/advanced");
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		if (is_array($general))	
		$data = array_merge($data, $general);
		if (is_array($detail_market)) 				
		$data = array_merge($data, $detail_market);
		if (is_array($social_market)) 				
		$data = array_merge($data, $social_market);
		if (is_array($advanced)) 				
		$data = array_merge($data, $advanced);
		
		return array_merge($data, $attributes);
	}
	
}
	 