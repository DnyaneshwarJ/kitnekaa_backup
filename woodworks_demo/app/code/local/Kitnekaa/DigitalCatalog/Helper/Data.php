<?php
class Kitnekaa_DigitalCatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function resizeImage($catID) {
		//Get Img URL For Given Category ID
		$catImgURL = Mage::getModel('catalog/category')->load($catID)->getImageUrl();

		if($catImgURL == '') {
			$imgPath = Mage::getBaseUrl('media') . 'images/pdf-icon.png';
		} else {
			$imgPath = $catImgURL;
		}

		$newImgURL = '';

		//if ($catImgURL != '') {
		if ($imgPath != '') {
			//Resized Image Directory Path
			$resizedImgDirPath = Mage::getBaseDir('media') . DS . 'digital_catalog'. DS . 'resized_category_images'. DS;

			//Create Directory To Save Resized Category Image
			if(!file_exists($resizedImgDirPath)) {
				mkdir($resizedImgDirPath, 0755, true);
			}

			//Get Image Name
			$imgName = substr(strrchr($imgPath, "/"), 1);

			//Resized Img URL
			$resizedImgURL = Mage::getBaseDir('media') . DS . 'digital_catalog'. DS . 'resized_category_images'. DS . $imgName;
			$dirImg = Mage::getBaseDir().str_replace("/",DS,strstr($imgPath,'/media'));

			if(!file_exists($resizedImgURL) && file_exists($dirImg)) {
				$imageObj = new Varien_Image($dirImg);
				$imageObj->constrainOnly(TRUE);
				$imageObj->keepAspectRatio(TRUE);
				$imageObj->keepFrame(TRUE);
				$imageObj->backgroundColor(array(255,255,255));
				$imageObj->keepTransparency(TRUE);
				$imageObj->resize(276, 183);
				$imageObj->save($resizedImgURL);
			}

			//New Img URL Path
			$newImgURL = Mage::getBaseUrl('media') . 'digital_catalog'. DS . 'resized_category_images'. DS . $imgName;
		}

		return $newImgURL;
	}
}
	 