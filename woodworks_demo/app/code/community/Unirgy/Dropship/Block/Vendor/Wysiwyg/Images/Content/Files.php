<?php

class Unirgy_Dropship_Block_Vendor_Wysiwyg_Images_Content_Files extends Mage_Adminhtml_Block_Template
{
    /**
     * Files collection object
     *
     * @var Varien_Data_Collection_Filesystem
     */
    protected $_filesCollection;

    /**
     * Prepared Files collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFiles()
    {
        if (! $this->_filesCollection) {
            $this->_filesCollection = Mage::getSingleton('udropship/wysiwyg_images_storage')->getFilesCollection(Mage::helper('udropship/wysiwyg_images')->getCurrentPath(), $this->_getMediaType());

        }

        return $this->_filesCollection;
    }

    /**
     * Files collection count getter
     *
     * @return int
     */
    public function getFilesCount()
    {
        return $this->getFiles()->count();
    }

    /**
     * File idetifier getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileId(Varien_Object $file)
    {
        return $file->getId();
    }

    /**
     * File thumb URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileThumbUrl(Varien_Object $file)
    {
        return $file->getThumbUrl();
    }

    /**
     * File name URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileName(Varien_Object $file)
    {
        return $file->getName();
    }

    /**
     * Image file width getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileWidth(Varien_Object $file)
    {
        return $file->getWidth();
    }

    /**
     * Image file height getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileHeight(Varien_Object $file)
    {
        return $file->getHeight();
    }

    /**
     * File short name getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileShortName(Varien_Object $file)
    {
        return $file->getShortName();
    }

    public function getImagesWidth()
    {
        return Mage::getSingleton('udropship/wysiwyg_images_storage')->getConfigData('resize_width');
    }

    public function getImagesHeight()
    {
        return Mage::getSingleton('udropship/wysiwyg_images_storage')->getConfigData('resize_height');
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
