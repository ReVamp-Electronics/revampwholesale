<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Attachment
 * @package Aheadworks\Rma\Model
 */
class Attachment extends \Magento\Framework\Model\AbstractModel
{
    const TMP_PATH = 'tmp/aw_rma/attachments';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            null,
            null,
            $data
        );
        $this->filesystem = $filesystem;
    }

    protected function _construct()
    {
        $this->_init('Aheadworks\Rma\Model\ResourceModel\Attachment');
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = $mediaDirectory->getAbsolutePath(\Aheadworks\Rma\Model\Attachment::TMP_PATH) . $this->getFile();

        if (file_exists($fileName)) {
            if ($this->getRemoved()) {
                $this->_dataSaveAllowed = false;
            } else {
                $this->setContent(@file_get_contents($fileName));
            }
            @unlink($fileName);
        }
        return parent::beforeSave();
    }

    public function getContentLength()
    {
        if (is_null($this->getData('content_length'))) {
            $this->setData('content_length', strlen($this->getContent()));
        }
        return $this->getData('content_length');
    }
}