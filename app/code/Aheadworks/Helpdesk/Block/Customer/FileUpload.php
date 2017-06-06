<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Block\Customer;

/**
 * Class FileUpload
 * @package Aheadworks\Helpdesk\Block\Customer
 */
class FileUpload extends \Magento\Framework\View\Element\Template
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'customer/file_upload.phtml';

    /**
     * Get upload url
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('*/*/upload', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
