<?php
/*------------------------------------------------------------------------
 # SM Deal - Version 1.0
 # Copyright (c) 2014 YouTech Company. All Rights Reserved.
 # @license - Copyrighted Commercial Software
 # Author: YouTech Company
 # Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

class Sm_Deal_Controller_Adminhtml_Deal extends Mage_Adminhtml_Controller_Action{

	protected function _uploadAndGetName($input, $destinationFolder, $data){
		try{
			if (isset($data[$input]['delete'])){
				return '';
			}
			else{
				$uploader = new Varien_File_Uploader($input);
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				$uploader->setAllowCreateFolders(true);
				$result = $uploader->save($destinationFolder);
				return $result['file'];
			}
		}
		catch (Exception $e){
			if ($e->getCode() != Varien_File_Uploader::TMP_NAME_EMPTY){
				throw $e;
			}
			else{
				if (isset($data[$input]['value'])){
					return $data[$input]['value'];
				}
			}
		}
		return '';
	}
}