<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Ticket;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Helpdesk\Helper\File as FileHelper;

/**
 * File upload
 * @package Aheadworks\Helpdesk\Controller\Ticket
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends \Magento\Framework\App\Action\Action
{
    /**
     * Store interface
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $store;

    /**
     * Result json factory
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Filesystem
     * @var Filesystem
     */
    private $filesystem;

    /**
     * File uploader factory
     * @var UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * File helper
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param FileHelper $fileHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        FileHelper $fileHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->store = $storeManager->getStore();
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $filesystem;
        $this->fileHelper = $fileHelper;
    }

    /**
     * Upload action
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var \Magento\Framework\File\Uploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create(['fileId' => 'file[0]']);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $fileUploader->save(
                $mediaDirectory->getAbsolutePath(\Aheadworks\Helpdesk\Model\Attachment::TMP_PATH)
            );

            $result['url'] = $this->store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . \Aheadworks\Helpdesk\Model\Attachment::TMP_PATH
                . DIRECTORY_SEPARATOR
                . $result['file'];
            $result['text_file_size'] = $this->fileHelper->getTextFileSize($result['size']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
