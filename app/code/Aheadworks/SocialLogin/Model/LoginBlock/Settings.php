<?php
namespace Aheadworks\SocialLogin\Model\LoginBlock;

/**
 * Class Settings
 */
class Settings
{
    /**
     * @var \Aheadworks\SocialLogin\Model\Config\LoginBlock
     */
    protected $loginBlockConfig;

    /**
     * @var \Aheadworks\SocialLogin\Model\LoginBlock\Template\Provider
     */
    protected $templateProvider;

    /**
     * @param \Aheadworks\SocialLogin\Model\Config\LoginBlock $loginBlockConfig
     * @param Template\Provider $templateProvider
     */
    public function __construct(
        \Aheadworks\SocialLogin\Model\Config\LoginBlock $loginBlockConfig,
        \Aheadworks\SocialLogin\Model\LoginBlock\Template\Provider $templateProvider
    ) {
        $this->loginBlockConfig = $loginBlockConfig;
        $this->templateProvider = $templateProvider;
    }

    /**
     * Is group visible
     *
     * @param string $group
     * @return bool
     */
    public function isGroupVisible($group)
    {
        $isVisible = $this->loginBlockConfig->isVisibleDefault();

        $settings = $this->loginBlockConfig->getGroupSettings($group);
        if (is_array($settings) && isset($settings['is_visible'])) {
            $isVisible = (bool)$settings['is_visible'];
        }

        return $isVisible;
    }

    /**
     * Get template instance
     *
     * @param string $group
     * @return Template
     * @throws \Aheadworks\SocialLogin\Exception\InvalidTemplateException
     */
    public function getGroupTemplate($group)
    {
        $templateId = $this->loginBlockConfig->getDefaultTemplate();

        $settings = $this->loginBlockConfig->getGroupSettings($group);
        if (is_array($settings) && isset($settings['template'])) {
            $templateId = $settings['template'];
        }

        return $this->templateProvider->getTemplateInstance($templateId);
    }
}
