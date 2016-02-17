<?php

class Unirgy_DropshipVendorProduct_Block_ProductViewMediaOSP extends OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Media
{
    protected $_mgiKey = 'unirgy_media_gallery_images';

    protected function _getProdMediaGallery($p, $isForJson=false)
    {
        $mg = $p->getMediaGallery('images');
        if (empty($mg) && $isForJson) {
            $mg = array(array());
        }
        return $mg;
    }

    public function _getGalleryImages($onlyMainUrl=true, $softIdentify=false, $fullData=false, $isForJson=false) {
        if ($this->getProduct()->getIsProductListFlag() && !$this->getProduct()->getMediaGalleryLoadedFlag()) {
            $this->getProduct()->setMediaGalleryLoadedFlag(true);
            $this->getProduct()->getResource()->getAttribute('media_gallery')->getBackend()->afterLoad($this->getProduct());
        }
        $p = $this->getProduct();
        $mgiKey = sprintf('%s%s%s%s%s', $this->_mgiKey, $onlyMainUrl, $softIdentify, $fullData, $isForJson);
        if(!$p->hasData($mgiKey) && (is_array($p->getMediaGallery('images')) || $isForJson)) {
            $defSuperAttributeKey = '';
            if ($p->getTypeId()=='configurable') {
                $swatchShowFlag = false; $_softIdentity = array();
                $cfgDefSel = array();
                $idx=0; foreach ($p->getTypeInstance(true)->getConfigurableAttributes($p) as $cfgAttr) {
                    $_aId = $cfgAttr->getAttributeId();
                    $_pAttr = $cfgAttr->getProductAttribute();
                    $lpcNoDisplayFlag = $swatchShowFlag || !$cfgAttr->getIdentifyImage() || !($swatchShowFlag = count($cfgAttr->getPrices())>1);
                    if (!$lpcNoDisplayFlag) $_softIdentity[] = $_pAttr->getId();

                    $cfgPrices = $cfgAttr->getPrices();
                    reset($cfgPrices);
                    $_cfgPrice = current($cfgPrices);
                    if (0 == $idx++) {
                        $usedValues = array();
                        $simpleProducts = $p->getTypeInstance(true)->getUsedProducts(null, $p);
                        foreach ($simpleProducts as $simpleProd) {
                            $usedValues[] = $simpleProd->getData($_pAttr->getAttributeCode());
                        }
                        $usedValues = array_unique($usedValues);
                        foreach ($usedValues as $usedValue) {
                            foreach ($cfgPrices as $cfgPrice) {
                                if ($cfgPrice['value_index'] == $usedValue) {
                                    $cfgDefSel[$_pAttr->getId()] = $cfgPrice['value_index'];
                                    break 2;
                                }
                            }
                        }
                    } else {
                    $cfgDefSel[$_pAttr->getId()] = $_cfgPrice['value_index'];
                    }

                }
                $defSuperAttributeKey = array();
                foreach ($p->getTypeInstance(true)->getConfigurableAttributes($p) as $cfgAttr) {
                    $_aId = $cfgAttr->getAttributeId();
                    $_pAttr = $cfgAttr->getProductAttribute();
                    if ((!$softIdentify && $cfgAttr->getIdentifyImage()) || in_array($_aId, $_softIdentity)) {
                        $defSuperAttributeKey[] = sprintf('%s=%s', $_aId, @$cfgDefSel[$_aId]);
                    }
                }
                $defSuperAttributeKey = implode(';', $defSuperAttributeKey);
                $defColor = $this->getDefaultColorByImage();
                $cfgFirstAttr = Mage::helper('udprod')->getCfgFirstAttribute($p);
                $cfgFirstAttrId = $cfgFirstAttr->getId();
            }
            $imagesByKey = $images = array();
            foreach ($this->_getProdMediaGallery($p, $isForJson) as $image) {
                if (!empty($image['disabled'])) {
                    continue;
                }
                $_img = $fullData ? $image : array();
                $gImage = new Varien_Object($image);

                $_img['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $_img['path'] = $p->getMediaConfig()->getMediaPath(@$image['file']);
                $_img['superAttribute'] = @$image['super_attribute'];
                $_img['superAttributeKey'] = array();
                $_img['superAttributeCode'] = array();
                $_img['superAttributeLabel'] = array();
                if ($p->getTypeId()=='configurable') {
                    foreach ($p->getTypeInstance(true)->getConfigurableAttributes($p) as $cfgAttr) {
                        $_aId = $cfgAttr->getAttributeId();
                        $_pAttr = $cfgAttr->getProductAttribute();
                        if ($cfgAttr->getIdentifyImage() && isset($_img['superAttribute'][$_aId])
                            && (!$softIdentify || in_array($_aId, $_softIdentity))
                        ) {
                            $_img['superAttributeKey'][] = sprintf('%s=%s', $_aId, $_img['superAttribute'][$_aId]);
                            $_img['superAttributeCode'][$_aId] = $_pAttr->getAttributeCode();
                            $_img['superAttributeLabel'][$_aId] = $_pAttr->getSource()->getOptionText($_img['superAttribute'][$_aId]);
                        }
                    }
                }
                $_img['superAttributeKey'] = implode(';', $_img['superAttributeKey']);

                $imgName = preg_replace('#(\.jpg).*$#i', '$1', @$image['file']);
                if (!empty($imagesByKey[$imgName])) {
                    $gImage = $imagesByKey[$imgName];
                } else {
                    $gImage = $this->_processImage($gImage->setOnlyMainUrlFlag($onlyMainUrl));
                }
                $_img['url'] = $gImage->getMedium();
                $_img['size'] = $gImage->getMediumSize();
                $_img['full_url'] = $gImage->getImg();
                $_img['full_size'] = $gImage->getImgSize();
                $_img['mid_url']  = $gImage->getMidImg();
                $_img['mid_size']  = $gImage->getMidImgSize();
                $_img['thumb_url']  = $gImage->getThumb();
                $_img['thumb_size']  = $gImage->getThumbSize();

                if ($p->getTypeId()=='configurable') {
                if (1
                    //&& $defSuperAttributeKey == $_img['superAttributeKey']
                    && @$image['file'] == $p->getThumbnail()
                    //&& @$_img['superAttribute']['main']
                ) {
                    $_img['superAttribute']['main'] = 1;
                    $this->setMainImg($gImage);
                } elseif ($cfgFirstAttrId && $defColor
                    && isset($image['super_attribute'][$cfgFirstAttrId])
                    && $image['super_attribute'][$cfgFirstAttrId] == $defColor
                ) {
                    $_img['superAttribute']['main'] = 0;
                }
                }

                $imagesByKey[$imgName] = $gImage;
                $images[] = $_img;
            }
            $p->setData($mgiKey, $images);
        } elseif (!$p->hasData($mgiKey)) {
            $p->setData($mgiKey, array());
        }

        return $p->getData($mgiKey);
    }

    public function getDefaultColorByImage()
    {
        return Mage::helper('udprod')->getDefaultColorByImage($this->getProduct());
    }

    public function getGalleryImagesJson($softIdentify=false) {
        return Mage::helper('core')->jsonEncode($this->_getGalleryImages(false, $softIdentify, false, true));
    }

    public function getDefaultImageUrl()
    {
        return $this->getMainImg()->getMedium();
    }

    protected $_thumbWidth  = 375;
    protected $_thumbHeight = 407;
    protected $_smallThumbWidth  = 61;
    protected $_smallThumbheight = 61;
    public function setThumbSize($width, $height)
    {
        $this->_thumbWidth = $width;
        $this->_thumbHeight = $height;
        return $this;
    }
    public function setSmallThumbSize($width, $height)
    {
        $this->_smallThumbWidth  = $width;
        $this->_smallThumbHeight = $height;
        return $this;
    }

    public function getThumbSize($width=true)
    {
        return $width ? $this->_thumbWidth : $this->_thumbHeight;
    }
    public function getSmallThumbSize($width=true)
    {
        return $width ? $this->_smallThumbWidth : $this->_smallThumbHeight;
    }

    protected $_mainImg;
    public function getMainImg($reload=false)
    {
        if (is_null($this->_mainImg) || $reload) {
            $this->_mainImg = $this->_processImage();
        }
        return $this->_mainImg;
    }
    public function setMainImg($img)
    {
        $this->_mainImg = $img;
        return $this;
    }

    protected function _processImage($gImage=null)
    {
        list($smallThumbWidth, $smallThumbHeight) = array($this->_smallThumbWidth, $this->_smallThumbHeight);
        list($thumbWidth, $thumbHeight) = array($this->_thumbWidth, $this->_thumbHeight);
        $thumbAspectRatio = $thumbWidth/$thumbHeight;

        if (is_null($gImage)) $gImage = new Varien_Object();
        if ($gImage->getUnirgyImgFlag()) return $gImage;

        $_product = $this->getProduct();

        if ($_product->getIsProductListFlag() || $gImage->getOnlyMainUrlFlag()) {
            $imageAttr = $_product->getIsProductListFlag() ? 'small_image' : 'image';
            if ('thumb' == $gImage->getOnlyMainUrlFlag()) {
                $gImage->setThumb(
                    $this->helper('catalog/image')->init($_product, $imageAttr, $gImage->getFile())
                        ->setQuality(100)->resize($smallThumbWidth, $smallThumbHeight)->__toString()
                );
                $gImage->setThumbSize(array($smallThumbWidth, $smallThumbHeight));
                $gImage->setMedium($gImage->getThumb());
                $gImage->setMediumSize($gImage->getThumbSize());
                $gImage->setUnirgyImgFlag(true);
            } else {
                $gImage->setMedium(
                    $this->helper('catalog/image')->init($_product, $imageAttr, $gImage->getFile())
                        ->setQuality(100)->resize($thumbWidth, $thumbHeight)->__toString()
                );
                $gImage->setMediumSize(array($thumbWidth, $thumbHeight));
                $gImage->setUnirgyImgFlag(true);
            }
            return $gImage;
        }

        $imgHlp = $this->helper('catalog/image')->init($_product, 'image', $gImage->getFile())->setQuality(100);
        $imgHlp->__toString();

        list($imgWidth, $imgHeight) = $imgHlp->getOriginalSizeArray();
        if (!$imgHeight) $imgHeight = 1;
        $maxDim = max($imgWidth, $imgHeight);
        if ($maxDim>2000) {
            $resizeRatio = $maxDim/2000;
            $imgWidth = round($imgWidth/$resizeRatio);
            $imgHeight = round($imgHeight/$resizeRatio);
        }
        $imgAspectRatio = $imgWidth/$imgHeight;
        if ($imgAspectRatio>$thumbAspectRatio) {
            $imgHeight = round($imgWidth/$thumbAspectRatio);
            $zoomValue = $imgWidth/$thumbWidth;
        } else {
            $imgWidth = round($imgHeight*$thumbAspectRatio);
            $zoomValue = $imgHeight/$thumbHeight;
        }
        $midZoomValue = 1+($zoomValue-1)/2;
        $midImgWidth  = round($thumbWidth*$midZoomValue);
        $midImgHeight = round($thumbHeight*$midZoomValue);

        if ($imgWidth<$thumbWidth || $imgHeight<$thumbHeight) {
            $midImgWidth = $imgWidth = $thumbWidth;
            $midImgHeight = $imgHeight = $thumbHeight;
        }

        list($img, $midImg, $medium, $thumb) = array(
            $this->helper('catalog/image')->init($_product, 'image', $gImage->getFile())->setQuality(100)->resize($imgWidth,$imgHeight)->__toString(),
            $this->helper('catalog/image')->init($_product, 'image', $gImage->getFile())->setQuality(100)->resize($midImgWidth,$midImgHeight)->__toString(),
            $this->helper('catalog/image')->init($_product, 'image', $gImage->getFile())->setQuality(100)->resize($thumbWidth, $thumbHeight)->__toString(),
            $this->helper('catalog/image')->init($_product, 'image', $gImage->getFile())->setQuality(100)->resize($smallThumbWidth, $smallThumbHeight)->__toString()
        );
        $gImage->setMidImg($midImg);
        $gImage->setMidImgSize(array($midImgWidth,$midImgHeight));
        $gImage->setImg($img);
        $gImage->setImgSize(array($imgWidth,$imgHeight));
        $gImage->setMedium($medium);
        $gImage->setMediumSize(array($thumbWidth, $thumbHeight));
        $gImage->setThumb($thumb);
        $gImage->setThumbSize(array($smallThumbWidth, $smallThumbHeight));
        $gImage->setUnirgyImgFlag(true);
        return $gImage;
    }

    public function getGalleryImages()
    {
        $mgiKey = $this->_mgiKey.'collection';
        if(!$this->getProduct()->hasData($mgiKey)) {
            $images = new Varien_Data_Collection();
            $usedKeys = array();
            foreach ($this->_getGalleryImages('thumb', false) as $image) {
                $imgName = preg_replace('#(\.jpg).*$#i', '$1', @$image['url']);
                if (!empty($usedKeys[$imgName])) continue;
                $usedKeys[$imgName] = 1;
                $gImage = new Varien_Object($image);
                $gImage->setThumb($gImage->getThumbUrl());
                $images->addItem($gImage);
            }
            $this->getProduct()->setData($mgiKey, $images);
        }

        return $this->getProduct()->getData($mgiKey);
    }

    public function getGalleryImagesThumbs()
    {
        return $this->getGalleryImages();
    }
}
