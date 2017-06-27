<?php
namespace Aheadworks\SocialLogin\Model\Provider;

/**
 * Abstract Provider Factory
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }
}
