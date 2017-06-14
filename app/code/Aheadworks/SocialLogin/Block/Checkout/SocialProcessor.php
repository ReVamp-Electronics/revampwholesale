<?php
namespace Aheadworks\SocialLogin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

/**
 * Class SocialProcessor
 */
class SocialProcessor implements LayoutProcessorInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $socialLinksComponent = &$jsLayout['components']['checkout']['children']['authentication']
        ['children']['social-fields'];
        $socialLinksComponent['linksContent'] = $this->getLinksBlock()->toHtml();
        return $jsLayout;
    }

    /**
     * Get links block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function getLinksBlock()
    {
        return $this->layout->createBlock(
            \Aheadworks\SocialLogin\Block\Customer\Login\Configurable::class,
            'customer.social.checkout',
            [
                'data' => [
                    'configurable_group' => 'Checkout',
                    'css_class' => 'social-login-checkout'
                ]
            ]
        );
    }
}
