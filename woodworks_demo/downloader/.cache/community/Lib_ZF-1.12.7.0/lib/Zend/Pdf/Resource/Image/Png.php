<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String/Binary.php';


/** Zend_Pdf_Resource_Image */
#require_once 'Zend/Pdf/Resource/Image.php';

/**
 * PNG image
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Image_Png extends Zend_Pdf_Resource_Image
{
    const PNG_COMPRESSION_DEFAULT_STRATEGY = 0;
    const PNG_COMPRESSION_FILTERED = 1;
    const PNG_COMPRESSION_HUFFMAN_ONLY = 2;
    const PNG_COMPRESSION_RLE = 3;

    const PNG_FILTER_NONE = 0;
    const PNG_FILTER_SUB = 1;
    const PNG_FILTER_UP = 2;
    const PNG_FILTER_AVERAGE = 3;
    const PNG_FILTER_PAETH = 4;

    const PNG_INTERLACING_DISABLED = 0;
    const PNG_INTERLACING_ENABLED = 1;

    const PNG_CHANNEL_GRAY = 0;
    const PNG_CHANNEL_RGB = 2;
    const PNG_CHANNEL_INDEXED = 3;
    const PNG_CHANNEL_GRAY_ALPHA = 4;
    const PNG_CHANNEL_RGB_ALPHA = 6;

    protected $_width;
    protected $_height;
    protected $_imageProperties;

    /**
     * Object constructor
     *
     * @param string $imageFileName
     * @throws Zend_Pdf_Exception
     * @todo Add compression conversions to support compression strategys other than PNG_COMPRESSION_DEFAULT_STRATEGY.
     * @todo Add pre-compression filtering.
     * @todo Add interlaced image handling.
     * @todo Add support for 16-bit images. Requires PDF version bump to 1.5 at least.
     * @todo Add processing for all PNG chunks defined in the spec. gAMA etc.
     * @todo Fix tRNS chunk support for Indexed Images to a SMask.
     */
    public function __construct($imageFileName)
    {
        if (($imageFile = @fopen($imageFileName, 'rb')) === false ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Can not open '$imageFileName' file for reading." );
        }

        parent::__construct();

        //Check if the file is a PNG
        fseek($imageFile, 1, SEEK_CUR); //First signature byte (%)
        if ('PNG' != fread($imageFile, 3)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Image is not a PNG');
        }
        fseek($imageFile, 12, SEEK_CUR); //Signature bytes (Includes the IHDR chunk) IHDR processed linerarly because it doesnt contain a variable chunk length
        $wtmp = unpack('Ni',fread($imageFile, 4)); //Unpack a 4-Byte Long
        $width = $wtmp['i'];
        $htmp = unpack('Ni',fread($imageFile, 4));
        $height = $htmp['i'];
        $bits = ord(fread($imageFile, 1)); //Higher than 8 bit depths are only supported in later versions of PDF.
        $color = ord(fread($imageFile, 1));

        $compression = ord(fread($imageFile, 1));
        $prefilter = ord(fread($imageFile,1));

        if (($interlacing = ord(fread($imageFile,1))) != Zend_Pdf_Resource_Image_Png::PNG_INTERLACING_DISABLED) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Only non-interlaced images are currently supported." );
        }

        $this->_width = $width;
        $this->_height = $height;
        $this->_imageProperties = array();
        $this->_imageProperties['bitDepth'] = $bits;
        $this->_imageProperties['pngColorType'] = $color;
        $this->_imageProperties['pngFilterType'] = $prefilter;
        $this->_imageProperties['pngCompressionType'] = $compression;
        $this->_imageProperties['pngInterlacingType'] = $interlacing;

        fseek($imageFile, 4, SEEK_CUR); //4 Byte Ending Sequence
        $imageData = '';

        /*
         * The following loop processes PNG chunks. 4 Byte Longs are packed first give the chunk length
         * followed by the chunk signature, a four byte code. IDAT and IEND are manditory in any PNG.
         */
        while (!feof($imageFile)) {
            $chunkLengthBytes = fread($imageFile, 4);
            if ($chunkLengthBytes === false) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Error ocuured while image file reading.');
            }

            $chunkLengthtmp = unpack('Ni', $chunkLengthBytes);
            $chunkLength    = $chunkLengthtmp['i'];
            $chunkType      = fread($imageFile, 4);
            switch($chunkType) {
                case 'IDAT': //Image Data
                    /*
                     * Reads the actual image data from the PNG file. Since we know at this point that the compression
                     * strategy is the default strategy, we also know that this data is Zip compressed. We will either copy
                     * the data directly to the PDF and provide the correct FlateDecode predictor, or decompress the data
                     * decode the filters and output the data as a raw pixel map.
                     */
                    $imageData .= fread($imageFile, $chunkLength);
                    fseek($imageFile, 4, SEEK_CUR);
                    break;

                case 'PLTE': //Palette
                    $paletteData = fread($imageFile, $chunkLength);
                    fseek($imageFile, 4, SEEK_CUR);
                    break;

                case 'tRNS': //Basic (non-alpha channel) transparency.
                    $trnsData = fread($imageFile, $chunkLength);
                    switch ($color) {
                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY:
                            $baseColor = ord(substr($trnsData, 1, 1));
                            $transparencyData = array(new Zend_Pdf_Element_Numeric($baseColor),
                                                      new Zend_Pdf_Element_Numeric($baseColor));
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB:
                            $red = ord(substr($trnsData,1,1));
                            $green = ord(substr($trnsData,3,1));
                            $blue = ord(substr($trnsData,5,1));
                            $transparencyData = array(new Zend_Pdf_Element_Numeric($red),
                                                      new Zend_Pdf_Element_Numeric($red),
                                                      new Zend_Pdf_Element_Numeric($green),
                                                      new Zend_Pdf_Element_Numeric($green),
                                                      new Zend_Pdf_Element_Numeric($blue),
                                                      new Zend_Pdf_Element_Numeric($blue));
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_INDEXED:
                            //Find the first transparent color in the index, we will mask that. (This is a bit of a hack. This should be a SMask and mask all entries values).
                            if(($trnsIdx = strpos($trnsData, "\0")) !== false) {
                                $transparencyData = array(new Zend_Pdf_Element_Numeric($trnsIdx),
                                                          new Zend_Pdf_Element_Numeric($trnsIdx));
                            }
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY_