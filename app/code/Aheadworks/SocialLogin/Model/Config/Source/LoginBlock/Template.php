<?php
namespace Aheadworks\SocialLogin\Model\Config\Source\LoginBlock;

use Aheadworks\SocialLogin\Model\LoginBlock\Template\Provider;

/**
 * Class Template
 */
class Template implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @param Provider $provider
     */
    public function __construct(
        Provider $provider
    ) {
        $this->provider = $provider;
    }
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $templatesData = $this->provider->getTemplatesData();
        $options = [];
        foreach ($templatesData as $templateId => $templateData) {
            $options[$templateId] = isset($templateData['title']) ? $templateData['title'] : $templateId;
        }
        return $options;
    }
}
