<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 04-02-2015
 * Time: 9:03
 */
class Sm_Cameraslide_Block_Adminhtml_Widget_Grid_Column_Renderer_Sliders_Thumb extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function _getValue(Varien_Object $row)
    {
        $params = Mage::helper('core')->jsonDecode($row->getParams());
        $httpBases = "https://";
        if(isset($params['data_thumb']) && $params['data_thumb'])
        {
            return sprintf('<img src="%s" height="200" width="500" />', strpos($params['data_thumb'], 'http') == 0 ? Mage::getBaseUrl('media').$params['data_thumb'] : $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg'));
        }
        elseif(isset($params['background_type']) && ($params['background_type']))
        {
            switch($params['background_type'])
            {
                case 'image' :
                    if(isset($params['data_src']) && $params['data_src'])
                    {
                        return sprintf('<img src="%s" height="200" width="500" style="margin: 4px auto;" />', strpos($params['data_src'], 'http') == 0 ? Mage::getBaseUrl('media').$params['data_src'] : $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg'));
                    }else{
                        return sprintf('<img src="%s" height="200" width="500" style="margin: 4px auto;" />', $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg'));
                    }
                    break;
                case 'color' :
                    if((isset($params['sliders_bg_color'])) && ($params['sliders_bg_color']))
                    {
                        return sprintf('<div style="height: 200px; width: 500px; background: #%s; margin: 4px auto;"></div>', $params['sliders_bg_color']);
                    }
                    break;
                case 'video' :

                    if((isset($params['service_video'])) && ($params['service_video'] == 'youtube'))
                    {
                        $loop = ($params['video_loop'] == 'loop') ? 'loop=1' : 'loop=0';
                        $controls = ($params['video_controls'] == 'controls') ? 'controls=1' : 'controls=0';
                        $autoplay = ($params['video_autoplay'] == 'autoplay') ? 'autoplay=1' : 'autoplay=0';
                        $autohide = ($params['video_autohide'] == '1') ? 'autohide=1' : 'autohide=0';
                        $showinfo = ($params['video_showinfo'] == '1') ? 'showinfo=1' : 'showinfo=0';
                        $coloryoutube = ($params['video_colorcontrolsyoutube'] == 'red') ? 'color=red' : 'color=white';
                        $src_video = $httpBases."www.youtube.com/embed/{$params['src_video']}".'?'.$autoplay.'&'.$controls.'&'.$loop.'&'.$showinfo.'&'.$autohide.'&'.$coloryoutube;
                        return sprintf('<iframe src="%s" height="200" width="500" webkitAllowFullScreen mozallowfullscreen allowFullScreen style="margin: 4px auto;"></iframe>', $src_video);
                    }
                    elseif((isset($params['service_video'])) && ($params['service_video'] == 'player.vimeo.com'))
                    {
                        $loop = ($params['video_loop'] == 'loop') ? 'loop=1' : 'loop=0';
                        $autoplay = ($params['video_autoplay'] == 'autoplay') ? 'autoplay=1' : 'autoplay=0';
                        $autopause = ($params['video_autopause'] == '1') ? 'autopause=1' : 'autopause=0';
                        $colorvimeo = ($params['video_colorcontrolsvimeo']) ? "color={$params['video_colorcontrolsvimeo']}" : 'color=00ADEF';
                        $src_video = $httpBases."player.vimeo.com/video/{$params['src_video_2']}".'?'.$autoplay.'&'.$loop.'&'.$colorvimeo.'&'.$autopause;
                        return sprintf('<iframe src="%s" height="200" width="500" webkitAllowFullScreen mozallowfullscreen allowFullScreen style="margin: 4px auto;"></iframe>', $src_video);
                    }
                    elseif((isset($params['service_video'])) && ($params['service_video'] == 'html5'))
                    {
                        $controls = $params['video_controls_html5'];
                        $loop = $params['video_loop'];
                        $autoplay = $params['video_autoplay'];
                        $muted = $params['video_muted'];
                        $videoMp4   = $params['html5_mp4_video'] ? "<source src=".Mage::getBaseUrl('media').$params['html5_mp4_video']." type='video/mp4' />" : '';
                        $videoWebm  = $params['html5_webm_video'] ? "<source src=".Mage::getBaseUrl('media').$params['html5_webm_video']." type='video/webm' />" : '';
                        $videoOgg   = $params['html5_ogg_video'] ? "<source src=".Mage::getBaseUrl('media').$params['html5_ogg_video']." type='video/ogg' />" : '';
                        if($params['html5_mp4_video'] || $params['html5_webm_video'] || $params['html5_ogg_video'])
                        {
                            return sprintf("<video height='200' width='500' $controls $loop $autoplay $muted>{$videoMp4} {$videoWebm} {$videoOgg}</video>");
                        }
                    }
                    break;
            }
        }else{
            return sprintf('<img src="%s" height="200" width="500" />', $this->getSkinUrl('sm/cameraslide/images/nophoto.jpg'));
        }
    }
}