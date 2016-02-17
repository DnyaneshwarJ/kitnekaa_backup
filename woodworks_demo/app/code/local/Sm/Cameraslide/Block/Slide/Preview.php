<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 10-05-2015
 * Time: 16:02
 */
class Sm_Cameraslide_Block_Slide_Preview extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface
{
	protected $slideHtmlId;
	protected $styleSlide;
	protected $slideHtmlIdWrapper;
	protected $numSliders;
	protected $customAnimations;

	protected function _construct()
	{
		parent::_construct();
	}

	protected function _prepareLayout()
	{
		if(Mage::helper('sm_cameraslide')->enabledCameraslide())
		{
			$head = $this->getLayout()->getBlock('head');
			if (Mage::app()->getRequest()->getActionName() == 'preview') {
				$head->addJs('sm/cameraslide/js/jquery-migrate-1.2.1.min.js');
				$head->addJs('sm/cameraslide/js/jquery-2.1.3.min.js');
				$head->addJs('sm/cameraslide/js/jquery-noconflict.js');
			}
			return parent::_prepareLayout();
		}
	}

	protected function _toHtml()
	{
		$dem = 0;
		$html = '';
		$output  = '';
		if(Mage::helper('sm_cameraslide')->enabledCameraslide())
		{
			if($this->getData('id'))
			{
				$ids = array($this->getData('id'));
			}
			elseif(Mage::helper('sm_cameraslide')->useWidget())
			{
				$ids = array($this->getData('id'));
			}
			else
			{
				$ids = Mage::helper('sm_cameraslide')->getSlide() ? explode(',', Mage::helper('sm_cameraslide')->getSlide()) : null;
			}


			foreach($ids as $id)
			{
				$modelSlide = Mage::getModel('sm_cameraslide/slide')->load($id);
				if($modelSlide->getId() && $modelSlide->getStatus() == 1) {
					$dem++;
					$scripts = array();
					foreach ($scripts as $script) {
						$html .= "<script type='text/javascript' src='{$script}'></script>";
					}
					$slideWidth1 = (int)$modelSlide->getData('slide_width') ? $modelSlide->getData('slide_width') : '960';
					$slideWidth = "width: {$slideWidth1}px;";
					$slideHeight = (int)$modelSlide->getData('slide_height') ? $modelSlide->getData('slide_height') : '574';
					$slideHeight = "height: {$slideHeight}px;";
					$this->slideHtmlId = "enfinity_" .$modelSlide->getId();
					$pagination = $modelSlide->getData('pagination');
					$data_time = (int)$modelSlide->getData('time_load') ? (int)$modelSlide->getData('time_load') : '7000';
					$data_transperiod = (int)$modelSlide->getData('trans_period') ? (int)$modelSlide->getData('trans_period') : '1500';
					$data_autoadv = $modelSlide->getData('auto_advance');
					$data_playpause = $modelSlide->getData('play_pause');
					$data_pagination = $modelSlide->getData('pagination');
					$data_prevnext = $modelSlide->getData('prev_next');
					if ($pagination == 'false') {
						$styleDivContainerSLide = "style='z-index:9999; clear: both;'";
					} else {
						$styleDivContainerSLide = "style='z-index:9999; clear: both;'";
					}

					$output .= $html;
					$output .= "<div {$styleDivContainerSLide}>";
					$output .= "<div id='$this->slideHtmlId' class='pix_slideshow' style='width:100%; visibility: visible;'>";
					$output .= "<div id='1_{$this->slideHtmlId}' class='pix_slideshow_target' style='{$slideWidth} {$slideHeight} visibility: visible;'  data-width='{$slideWidth1}' data-time='{$data_time}' data-transperiod='{$data_transperiod}' data-autoadvance='{$data_autoadv}' data-playpause='{$data_playpause}' data-prevnext='{$data_prevnext}' data-pagination='{$data_pagination}'>";
					$output .= $this->renderSliders($modelSlide);
					$output .= "</div>";
					$output .= "<div class='filmore_commands filmore_autoadv'>";
					if ($data_playpause == 'true') {
						$output .= "<a href='#' class='filmore_pause  filmore_command' style=''>Pause</a>";
						$output .= "<a href='#' class='filmore_play filmore_command' style=''>Play</a>";
					}
					if ($data_prevnext == 'true') {
						$output .= "<a href='#' class='filmore_prev filmore_command' style=''>Prev</a>";
					}
					$output .= "<span class='filmore_pagination'>";
					$output .= "</span>";
					if ($data_prevnext == 'true') {
						$output .= "<a href='#' class='filmore_next filmore_command'>Next</a>";
					}
					if ($data_autoadv == 'true')
					{
						$output .= "<div class='filmore_loader'>";
						$output .= "</div>";
					}else{
						$output .= "<div class='filmore_loader' style='visibility: hidden;'>";
						$output .= "</div>";
					}
					$output .= "</div>";
					$output .= "</div>";
					$output .= "</div>";
					$output .= $this->renderJsEnfinity();
					$output .= "<div class='clearfix_cameraslide cameraslide'></div>";
				}
			}
			return $output;
		}
	}

	private function renderJsEnfinity()
	{
		return "<script type='text/javascript'>
        jQuery(function()
        {
            jQuery('#{$this->slideHtmlId}').each(function()
            {
                var e = jQuery('#1_{$this->slideHtmlId}', this),
                t = parseFloat(e.attr('data-time')),
                a = parseFloat(e.attr('data-transperiod')),
                i = 'true' == e.attr('data-prevnext') ? jQuery('.filmore_prev', this) : '',
                r = 'true' == e.attr('data-prevnext') ? jQuery('.filmore_next', this) : '',
                o = 'true' == e.attr('data-playpause') ? jQuery('.filmore_pause', this) : '',
                s = 'true' == e.attr('data-playpause') ? jQuery('.filmore_play', this) : '',
                n = 'true' == e.attr('data-pagination') ? jQuery('.filmore_pagination', this) : '',
                u = jQuery('.filmore_loader', this),
                l = 'true' == e.attr('data-autoadvance') ? !0 : !1;
                e.filmore(
                    {
                        time: t,
                        transPeriod: a,
                        prev: i,
                        next: r,
                        pause: o,
                        play: s,
                        pagination: n,
                        loader: u,
                        autoadv: l,
                        slide_id: '#{$this->slideHtmlId}'
                    })
            })
        });</script>";
	}

	public function renderSliders($modelSlide)
	{
		$sliders            = $modelSlide->getAllSliders(true);
		$this->numSliders   = count($sliders);
		$duration           = $modelSlide->getData('delay_load');
		$output             = '';
		if($modelSlide && $this->numSliders)
		{
			$index = 0;
			foreach($sliders as $slider)
			{
				$bgtype     = $slider->getData('background_type');
				$load_bg_color = $slider->getData('sliders_bg_color');
				$styleImage = '';
				switch($bgtype)
				{
					case 'image':
						$urlSlideImage = $slider->getData( 'data_src') ? Mage::getBaseUrl( 'media' ) . $slider->getData( 'data_src' ) : $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg');
						break;
					case 'color':
						$urlSlideImage = Mage::getBaseUrl('js').'sm/cameraslide/js_plugin/images/transparent.png';
						if ($load_bg_color)
							$styleImage = "background: #{$load_bg_color};";
						else
							$styleImage = "background: transparent;";
						break;
					case 'video':
						$urlSlideImage = strpos( $slider->getData( 'data_src_video' ), 'http' ) == 0 ? Mage::getBaseUrl( 'media' ) . $slider->getData( 'data_src_video' ) : $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg');
						break;
				}

				$output .= "<div>";
				$output .= "<div style='{$styleImage}' data-src='{$urlSlideImage}' data-use='background'></div>";
				$output .= $this->renderLayers($slider);
				$output .= "</div>";
				$index++;
			}
		}else{
			$output = '<div class="no-sliders-text" style="display: block; color: #ffffff;">';
			$output .= $this->__('No find the sliders, please you add sliders');
			$output .= '</div>';
		}
		return $output;
	}

    public function getHtml5Sliders($layer)
    {
        $mediaUrl       = Mage::getBaseUrl( 'media' );
	    $video_data     = $layer->getData('video_data');
	    $order = (int)$layer->getData('order');

        $urlMp4         = $video_data['urlMp4'];
        $htmlMp4        = $urlMp4 ? '<source src='.$mediaUrl.$urlMp4.' type="video/mp4" />' : '';

        $urlWebm        = $video_data['urlWebm'];
        $htmlWebm       = $urlWebm ? '<source src='.$mediaUrl.$urlWebm.' type="video/webm" />' : '';

        $urlOgg         = $video_data['urlOgg'];
        $htmlOgg        = $urlOgg ? '<source src='.$mediaUrl.$urlOgg.' type="video/ogg" />' : '';

        $videoLoop      = $video_data['loop'] ? 'loop' : '';
        $videoControls  = $video_data['controls'] ? 'controls' : '';
        $videoAutoPlay  = $video_data['autoplay'] ? 'autoplay' : '';
        $videoMuted     = $video_data['muted'] ? 'muted' : '';

        $video_width = $video_data['width'] ? $video_data['width'] : '100%';
        $video_height = $video_data['height'] ? $video_data['height'] : '100%';
        $video_class = $layer->getData('class') ? $layer->getData('class') : '';
        $video_left = $layer->getData('left') ? 'left:'.$layer->getData('left').'px;' : 'left:auto;';
        $video_top = $layer->getData('top') ? 'top:'.$layer->getData('top').'px;' : 'top:auto;';

        $html           = "<div class='video_html5_cameraslide'>";
        $html           .= "<video width='100%' height='100%' $videoAutoPlay $videoControls $videoLoop $videoMuted style='z-index:{$order}; position: absolute; overflow: hidden;'>";
        $html           .= $htmlMp4;
        $html           .= $htmlWebm;
        $html           .= $htmlOgg;
        $html           .= "</video>";
        $html           .= "</div>";

        return $html;
    }

	public function renderLayers($slider)
	{
		if(!$slider->getLayers())
			return '';
		$output = '';
		$zIndex = 2;
		$styleCss = "position: absolute; z-index:999; padding:0;";
		foreach($slider->getLayers() as $layer)
		{
			$layer = new Varien_Object($layer);
			$type = $layer->getData('type');
			$layer_text = $layer->getData('text');
			$order = (int)$layer->getData('order');
			$float = "float:left;";
			$left1 = $layer->getData('left');
			$left = $left1 ? "left:{$left1};" : '';

			$right = 'right: auto;';

			$top1 = $layer->getData('top');
			$top = $top1 ? "top:{$top1};" : '';

			$bottom = 'bottom: auto;';

			$width = $layer->getData('width');
			$width_layer = $width ? "width:{$width}%;" : '';

			$height = $layer->getData('height');
			$height_layer = $height ? "height:{$height}%;" : '';

			$min_width = $layer->getData('min_width');
			$min_width = $min_width ? "min-width:{$min_width}%;" : '';

			$min_height = $layer->getData('min_height');
			$min_height = $min_height ? "min-height:{$min_height}%;" : '';

			if($type == "text")
			{
				$bg_color = $layer->getData('bg_color') ? (($layer->getData('bg_color') != 'transparent' ) ? "background:#{$layer->getData('bg_color')};" : 'background: transparent;') : '';
			}
			else
			{
				$bg_color = "background: transparent;";
			}

			$color = $layer->getData('color');
			$color = $color ? "color:#{$color};" : '';

			$font_family = $layer->getData('font_family');
			$font_family = $font_family ? "font-family:{$font_family};" : 'font-family: "Helvetica Neue", Verdana, Arial, sans-serif;';

			$font_size1 = (int)$layer->getData('font_size');
			$font_size = $font_size1 ? "font-size:{$font_size1}px;" : '';

			$text_align = $layer->getData('text_align');
			$text_align = $text_align ? "text-align:{$text_align};" : '';


			$textBold = $layer->getData('textBold');
			$textBold = $textBold ? "font-weight: {$textBold};" : '';

			$textItalic = $layer->getData('textItalic');
			$textItalic = $textItalic ? "font-style: {$textItalic};" : '';

			$textUnderline = $layer->getData('textUnderline');
			$textUnderline = $textUnderline ? "text-decoration: {$textUnderline};" : '';

			$class_layer = $layer->getData('class');
			$time_delay_transitions = $layer->getData('time_delay_transitions') ? $layer->getData('time_delay_transitions') : '1500';
			$time_transitions = $layer->getData('time_transitions') ? $layer->getData('time_transitions') : '500';
			$data_easein = $layer->getData('data_easein');
			$data_easeout = $layer->getData('data_easeout');
			$data_fxin = $layer->getData('data_fxin');
			$data_fxout = $layer->getData('data_fxout');
			$data_fadein = $layer->getData('data_fadein');
			$data_fadeout = $layer->getData('data_fadeout');
			$id = $type.'_'.$order;
			$classes = $layer->getData('classes');
			if(($classes == 'hide'))
			{
				$visibility = "visibility: hidden;";
			}else{
				$visibility = "visibility: visible;";
			}
			$enable_link = $layer->getData('enable_link');
			$title = $layer->getData('title_link');
			$title = $title ? $title : '';
			$alt = $layer->getData('alt_image');
			$alt = $alt ? "alt='{$alt}'" : "alt=''";
			$target = $layer->getData('target_link');
			$target = $target ? "target='{$target}'" : '';
			$link_http1 = $layer->getData('link');
			$type_link = array('http://', 'https://');
			$error_type_link = array();
			foreach ($type_link as $l)
			{
				if(!((string)strpos($link_http1, $l) === '0'))
				{
					$error_type_link[] = 1;
				}
			}

			if (count($error_type_link) == 2)
			{
				$link_http = "http://{$link_http1}";
			}
			else
			{
				$link_http = $link_http1;
			}

			$html = '';
			$styleCss .= "{$visibility}{$float}{$left}{$right}{$top}{$bottom}{$width_layer}{$height_layer}{$min_width}{$min_height}{$bg_color}{$color}{$font_family}{$font_size}{$text_align}{$textBold}{$textItalic}{$textUnderline}";
			$html_link_text = "<a href='{$link_http}' title='{$title}' {$target}>{$title}</a>";
			$html_text = $layer_text;
			$src_url = Mage::getBaseUrl('media').$layer->getData('image_url');
			$html_image = "<img src='{$src_url}' style='position: static;{$styleCss}' />";

			if($type == 'text')
			{
				$fsize = (int)$layer->getData('font_size');
				$output .= "<div class='filmore_caption' data-fontsize='{$fsize}' style='opacity:0;{$font_family}{$width_layer}{$height_layer}{$min_width}{$min_height}' data-use='caption' data-style='left:{$left1},top:{$top1}' data-delay='{$time_delay_transitions}' data-time='{$time_transitions}' data-easein='{$data_easein}' data-easeout='{$data_easeout}' data-fxin='{$data_fxin}' data-fxout='{$data_fxout}' data-fadein='{$data_fadein}' data-fadeout='{$data_fadeout}'>";
				$output .= "<em  style='position: relative;z-index:{$order};{$visibility}{$font_size}{$float}{$left}{$right}{$top}{$bottom}width:100%;height:100%;{$min_width}{$min_height}{$bg_color}{$color}{$text_align}{$textBold}{$textItalic}{$textUnderline}' class='{$class_layer}'>$html_text</em>";
				$output .= "</div>";
			}
			elseif ($type == 'image')
			{
				$output .= "<div class='dataLoaded filmoreLoaded' style='opacity:0;{$visibility}{$float}{$left}{$right}{$top}{$bottom}{$width_layer}{$height_layer}{$min_width}{$min_height}{$bg_color}' data-src='{$src_url}' data-use='simple' data-style='left:{$left1},top:{$top1}' data-delay='{$time_delay_transitions}' data-time='{$time_transitions}' data-easein='{$data_easein}' data-easeout='{$data_easeout}' data-fxin='{$data_fxin}' data-fxout='{$data_fxout}' data-fadein='{$data_fadein}' data-fadeout='{$data_fadeout}'>";
				if ($link_http1)
				{
					$output .= "<a href='{$link_http}' {$alt} {$target}>";
				}
				if($width && $height)
					$output .= "<img style='position: relative;z-index:{$order};{$visibility}width:100%;height:100%;' src='{$src_url}' {$alt} class='{$class_layer}'/>";
				elseif($width)
					$output .= "<img style='position: relative;z-index:{$order};{$visibility}width:100%;' src='{$src_url}' {$alt} class='{$class_layer}'/>";
				elseif($height)
					$output .= "<img style='position: relative;z-index:{$order};{$visibility}height:100%;' src='{$src_url}' {$alt} class='{$class_layer}'/>";
				else
					$output .= "<img style='position: relative;z-index:{$order};{$visibility}' src='{$src_url}' {$alt} class='{$class_layer}'/>";

				if ($link_http1)
				{
					$output .= "</a>";
				}
				$output .= "</div>";
			}
			elseif ($type == 'video')
			{
				$htmlVideo = "";
				$video_width = $layer->getData('video_width') ? $layer->getData('video_width') : '100%';
				$video_height = $layer->getData('video_height') ? $layer->getData('video_height') : '100%';
				$video_class = $layer->getData('class') ? $layer->getData('class') : '';
				$video_left = $layer->getData('left') ? 'left:'.$layer->getData('left').';' : 'left:auto;';
				$video_top = $layer->getData('top') ? 'top:'.$layer->getData('top').';' : 'top:auto;';
				$httpBases = 'https://';
				$service_video = $layer->getData('service_video');
				$video_data = $layer->getData('video_data');
				if((isset($service_video)) && ($service_video == 'youtube'))
				{
					$loop = ($video_data['loop']) ? 'loop=1' : 'loop=0';
					$autoplay = ($video_data['autoplay']) ? 'autoplay=1' : 'autoplay=0';
					$controls = ($video_data['controls']) ? 'controls=1' : 'controls=0';
					$nophoto = $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg');
					$src_bg_video = ($layer->getData('video_image_url')) ? $layer->getData('video_image_url') : "{$nophoto}";
					$src_video = $httpBases."www.youtube.com/embed/{$layer->getData('src_video')}".'?'.$controls.'&'.$autoplay.'&'.$loop;
					$htmlVideo .= "<iframe class='{$video_class}' src='{$src_video}' width='{$video_width}' height='{$video_height}' style='z-index:{$order};{$video_left}{$video_top}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
				}
				elseif((isset($service_video)) && ($service_video == 'vimeo'))
				{
					$loop = ($video_data['loop']) ? 'loop=1' : 'loop=0';
					$autoplay = ($video_data['autoplay']) ? 'autoplay=1' : 'autoplay=0';
					$nophoto = $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg');
					$src_bg_video = ($layer->getData('video_image_url')) ? $layer->getData('video_image_url') : "{$nophoto}";
					$src_video = $httpBases."player.vimeo.com/video/{$layer->getData('video_id')}".'?'.$autoplay.'&'.$loop;
					$htmlVideo .= "<iframe class='{$video_class}' src='{$src_video}' width='{$video_width}' height='{$video_height}' style='z-index:{$order};{$video_left}{$video_top}' frameborder='0'></iframe>";
				}
				elseif((isset($service_video)) && ($service_video == 'html5')){
					$url_media = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
					$nophoto = $this->getSkinUrl('sm/cameraslide/images/trans_tile2.png');
					$src_bg_video = ($video_data['urlPoster']) ? $url_media.$video_data['urlPoster'] : "{$nophoto}";
					$htmlVideo .= $this->getHtml5Sliders($layer);
				}
				$output .= "<div class='' style='opacity:0;z-index:{$order};{$visibility}{$float}{$left}{$right}{$top}{$bottom}width:{$video_width}%;height:{$video_height}%;{$min_width}{$min_height}{$bg_color}' data-src='{$src_bg_video}' data-use='video' data-style='left:{$left1},top:{$top1}' data-delay='{$time_delay_transitions}' data-time='{$time_transitions}' data-easein='{$data_easein}' data-easeout='{$data_easeout}' data-fxin='{$data_fxin}' data-fxout='{$data_fxout}' data-fadein='{$data_fadein}' data-fadeout='{$data_fadeout}'>";
				$output .= $htmlVideo;
				$output .= "</div>";
			}
			$zIndex++;
		}
		return $output;
	}
}