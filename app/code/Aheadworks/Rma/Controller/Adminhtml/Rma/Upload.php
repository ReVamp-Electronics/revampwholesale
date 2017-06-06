<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Upload
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class Upload extends \Aheadworks\Rma\Controller\Adminhtml\Rma
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Aheadworks\Rma\Model\Attachment\FileUploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Aheadworks\Rma\Model\Attachment\FileUploaderFactory $fileUploaderFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Aheadworks\Rma\Model\Attachment\FileUploaderFactory $fileUploaderFactory
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
    }
    /**
     * @return $this
     */
    public function execute()
    {
        try {
            /** @var \Aheadworks\Rma\Model\Attachment\FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create(['fileId' => 'file[0]']);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $fileUploader->save($mediaDirectory->getAbsolutePath(\Aheadworks\Rma\Model\Attachment::TMP_PATH));

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
