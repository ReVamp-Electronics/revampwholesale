<?php
namespace Aheadworks\SocialLogin\Plugin\Customer\Block\Adminhtml\Edit;

use Magento\Customer\Block\Adminhtml\Edit\BackButton;
use Aheadworks\SocialLogin\Ui\Component\Listing\Column\AccountActions;

/**
 * Class BackButtonPlugin
 */
class BackButtonPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param BackButton $subject
     * @param string $backUrl
     * @return string
     */
    public function afterGetBackUrl(BackButton $subject, $backUrl)
    {
        if ($this->request->getParam('back') === AccountActions::URL_BACK_SOCIAL_PARAM_VALUE) {
            $backUrl = $subject->getUrl('social/account');
        }
        return $backUrl;
    }
}
