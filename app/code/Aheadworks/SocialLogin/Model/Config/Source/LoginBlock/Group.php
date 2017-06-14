<?php
namespace Aheadworks\SocialLogin\Model\Config\Source\LoginBlock;

/**
 * Class Group
 */
class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Aheadworks\SocialLogin\Model\Config\LoginBlock
     */
    protected $loginBlockConfig;

    /**
     * @param \Aheadworks\SocialLogin\Model\Config\LoginBlock $loginBlockConfig
     */
    public function __construct(
        \Aheadworks\SocialLogin\Model\Config\LoginBlock $loginBlockConfig
    ) {
        $this->loginBlockConfig = $loginBlockConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $groups = $this->loginBlockConfig->getGroups();
        $options = [];
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $options[] = [
                    'label' => $group,
                    'value' => $group
                ];
            }
        }
        return $options;
    }
}
