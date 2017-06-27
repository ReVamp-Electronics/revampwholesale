<?php
namespace Aheadworks\SocialLogin\Model;

use Aheadworks\SocialLogin\Exception\UnknownProviderException;
use Aheadworks\SocialLogin\Model\Provider\FactoryInterface;

/**
 * Class ProviderManagement
 */
class ProviderManagement
{
    /**
     * @var array
     */
    protected $factories;

    /**
     * @param array $factories
     */
    public function __construct(array $factories = [])
    {
        $this->factories = $this->sortFactories($factories);
    }

    /**
     * Get provider factory
     *
     * @param string $type
     * @return FactoryInterface
     * @throws UnknownProviderException
     */
    public function getFactory($type)
    {
        if (!isset($this->factories[$type])) {
            throw new UnknownProviderException(__('Provider with type "%1" not defined', $type));
        }
        return $this->factories[$type];
    }

    /**
     * Get enabled provider factory
     *
     * @param string $type
     * @return FactoryInterface
     * @throws UnknownProviderException
     */
    public function getEnabledFactory($type)
    {
        $factory = $this->getFactory($type);
        if (!$factory->getConfig()->isEnabled()) {
            throw new UnknownProviderException(__('Provider with type "%1" not enabled', $type));
        }
        return $factory;
    }

    /**
     * Get provider factories list
     *
     * @return FactoryInterface[]
     */
    public function getList()
    {
        return $this->factories;
    }

    /**
     * Get enabled provider factories list
     *
     * @return FactoryInterface[]
     */
    public function getEnabledList()
    {
        $enabledFactories = [];

        /**
         * @var string $code
         * @var FactoryInterface $factory
         */
        foreach ($this->factories as $factory) {
            if ($factory->getConfig()->isEnabled()) {
                $enabledFactories[] = $factory;
            }
        }

        return $enabledFactories;
    }

    /**
     * @param FactoryInterface[] $factories
     * @return FactoryInterface[]
     */
    protected function sortFactories(array $factories)
    {
        $factories = array_reverse($factories);
        uasort($factories, function ($factory1, $factory2) {
            /** @var FactoryInterface $factory1 */
            /** @var FactoryInterface $factory2 */
            return $factory1->getConfig()->getSortOrder() - $factory2->getConfig()->getSortOrder();
        });
        return $factories;
    }
}
