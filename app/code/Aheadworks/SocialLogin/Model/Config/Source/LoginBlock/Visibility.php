<?php
namespace Aheadworks\SocialLogin\Model\Config\Source\LoginBlock;

/**
 * Class Visibility
 */
class Visibility implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Visible')],
            ['value' => 0, 'label' => __('Not Visible')]
        ];
    }
}
