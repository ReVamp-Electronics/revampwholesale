<?php

namespace MW\RewardPoints\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
	) {
		$this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
		parent::__construct($context);
	}

	/**
	 * Get media directory
	 *
	 * @return string
	 */
	public function getMediaDirectory()
	{
		return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
	}

	/**
	 * Import Product Points via CSV file
	 *
	 * @param  array $fileData
	 * @return string $fileName
	 */
	public function importProductPoints($fileData)
	{
		/* Starting upload */
		/** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
		$uploader = $this->_fileUploaderFactory->create(['fileId' => 'filename']);
        // Any extention would work
        $uploader->setAllowedExtensions(['csv']);
        $uploader->setAllowRenameFiles(false);
        // Set the file upload mode
        // false -> get the file directly in the specified folder
        // true -> get the file in the product like folders
        // (file.jpg will go in something like /media/f/i/file.jpg)
        $uploader->setFilesDispersion(false);

        // We set media as the upload dir
        $path = $this->getMediaDirectory();
        $uploader->save($path, $fileData['filename']['name']);
        $fileName = $path . $uploader->getUploadedFileName();

        return $fileName;
	}

	/**
	 * Save promotion image
	 *
	 * @param  array $imageData
	 * @return string $fileName
	 */
	public function savePromotionImage($imageData)
	{
		/* Starting upload */
		/** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
		$uploader = $this->_fileUploaderFactory->create(['fileId' => 'promotion_image']);
        // Any extention would work
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'bmp']);
        $uploader->setAllowRenameFiles(true);
        // Set the file upload mode
        // false -> get the file directly in the specified folder
        // true -> get the file in the product like folders
        //	(file.jpg will go in something like /media/f/i/file.jpg)
        $uploader->setFilesDispersion(false);
        $fileName = $uploader->getCorrectFileName($imageData['promotion_image']['name']);
        // We set media as the upload dir
        $path = $this->getMediaDirectory() . '/mw_rewardpoint';
        $uploader->save($path, $fileName);

        return $fileName;
	}
}
