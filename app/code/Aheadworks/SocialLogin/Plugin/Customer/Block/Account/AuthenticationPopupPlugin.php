<?php
namespace Aheadworks\SocialLogin\Plugin\Customer\Block\Account;

/**
 * Class AuthenticationPopupPlugin
 */
class AuthenticationPopupPlugin
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
     * @param \Magento\Customer\Block\Account\AuthenticationPopup $subject
     * @param string $encodedJsLayout
     * @return string
     * @throws \Zend_Json_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsLayout(
        \Magento\Customer\Block\Account\AuthenticationPopup $subject,
        $encodedJsLayout
    ) {
        $jsLayout = \Zend_Json::decode($encodedJsLayout);

        $socialLinksComponent = &$jsLayout['components']['authenticationPopup']['children']['social-fields'];
        $socialLinksComponent['linksContent'] = $this->getLinksBlock()->toHtml();

        return \Zend_Json::encode($jsLayout);
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
            'customer.social.checkout.cart.popup',
            [
                'data' => [
                    'configurable_group' => 'Checkout',
                    'css_class' => 'social-login-checkout'
                ]
            ]
        );
    }
}
