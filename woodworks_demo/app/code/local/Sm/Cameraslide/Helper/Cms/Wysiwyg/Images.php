<?php
/**
 * Created by PhpStorm.
 * User: Vu Van Phan
 * Date: 05-02-2015
 * Time: 9:24
 */
class Sm_Cameraslide_Helper_Cms_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{

    public function isUsingStaticUrlsAllowed()
    {
        if ( Mage::getSingleton( 'adminhtml/session' )->getStaticUrlsAllowed() )
        {
            return true;
        }
        return parent::isUsingStaticUrlsAllowed();
    }

    public function getImageHtmlDeclaration( $filename, $renderAsTag = false )
    {
        $fileurl   = $this->getCurrentUrl() . $filename;
        $mediaPath = str_replace( Mage::getBaseUrl( 'media' ), '', $fileurl );
        $directive = sprintf( '{{media url="%s"}}', $mediaPath );
        if ( $renderAsTag )
        {
            $html = sprintf( '<img src="%s" alt="" />', $this->isUsingStaticUrlsAllowed() ? $fileurl : $directive );
        }
        else
        {
            if ( $this->isUsingStaticUrlsAllowed() )
            {
                $html = $mediaPath;
            }
            else
            {
                $directive = Mage::helper( 'core' )->urlEncode( $directive );
                $html      = Mage::helper( 'adminhtml' )->getUrl( '*/cms_wysiwyg/directive', array(
                    '___directive' => $directive
                ) );
            }
        }
        return $html;
    }
}
