<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Guest\Request;

/**
 * Class NewRequest
 * @package Aheadworks\Rma\Block\Guest\Request
 */
class NewRequest extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEXT_PAGE_BLOCK = 'aw_rma/blocks_and_policy/guest_rma_block';

    /**
     * @var string
     */
    protected $_template = 'guest/request/newrequest.phtml';

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Aheadworks\Rma\Helper\CmsBlock
     */
    protected $cmsBlockHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        \Aheadworks\Rma\Helper\CmsBlock $cmsBlockHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerUrl = $customerUrl;
        $this->cmsBlockHelper = $cmsBlockHelper;
    }

    /**
     * @return string
     */
    public function getTextCmsBlockHtml()
    {
        return $this->cmsBlockHelper->getBlockHtml(self::XML_PATH_TEXT_PAGE_BLOCK);
    }

    /**
     * @return string
     */
    public function getLoginPostUrl()
    {
        return $this->customerUrl->getLoginPostUrl();
    }

    /**
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->customerUrl->getForgotPasswordUrl();
    }

    /**
     * @return string
     */
    public function getNextPostUrl()
    {
        return $this->getUrl('*/*/createRequest');
    }
}
