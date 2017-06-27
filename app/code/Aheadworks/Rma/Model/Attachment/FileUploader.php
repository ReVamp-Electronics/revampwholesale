<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Attachment;

/**
 * Class FileUploader
 * @package Aheadworks\Rma\Model\Attachment
 */
class FileUploader extends \Magento\Framework\File\Uploader
{
    /**
     * @var bool
     */
    protected $_allowRenameFiles = false;

    /**
     * @var bool
     */
    protected $_enableFilesDispersion = true;

    /**
     * @var null
     */
    protected $_allowedExtensions = null;

    /**
     * @var \Aheadworks\Rma\Helper\File
     */
    protected $fileHelper;

    /**
     * @param array|string $fileId
     * @param \Aheadworks\Rma\Helper\File $fileHelper
     */
    public function __construct(
        $fileId,
        \Aheadworks\Rma\Helper\File $fileHelper
    ) {
        parent::__construct($fileId);
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param array $result
     * @return $this
     */
    protected function _afterSave($result)
    {
        $this->_result['text_file_size'] = $this->fileHelper->getTextFileSize($this->_file['size']);
        return parent::_afterSave($result);
    }
}
