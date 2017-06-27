<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block;

/**
 * Class FileUpload
 * @package Aheadworks\Rma\Block
 */
class FileUpload extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'fileupload.phtml';

    /**
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/*/upload');
    }
}
