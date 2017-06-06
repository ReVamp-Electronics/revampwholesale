<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Ticket;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Upload
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
 */
class Upload extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Store interface
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store;

    /**
     * Result json factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Filesystem
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Upload factory
     * @var UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * File helper
     * @var \Aheadworks\Helpdesk\Helper\File
     */
    protected $fileHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Helpdesk\Helper\File $fileHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Helpdesk\Helper\File $fileHelper
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;

        $this->store = $storeManager->getStore();
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $filesystem;
        $this->fileHelper = $fileHelper;
    }

    /**
     * Upload action
     * @return $this|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try {
            /** @var \Magento\Framework\File\Uploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create(['fileId' => 'file[0]']);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $fileUploader->save($mediaDirectory->getAbsolutePath(\Aheadworks\Helpdesk\Model\Attachment::TMP_PATH));

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
