<?php
namespace Aheadworks\SocialLogin\Model\Provider;

use Aheadworks\SocialLogin\Model\Provider\Service\Config\ConfigInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\Credentials\CredentialsInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\Storage\StorageInterface;

/**
 * Class ServiceBuilder
 */
abstract class ServiceBuilder implements ServiceBuilderInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $objectManager;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $service;

    /**
     * @param \Magento\Framework\ObjectManager\ObjectManager $objectManager
     * @param StorageInterface $storage
     * @param CredentialsInterface $credentials
     * @param ConfigInterface $config
     * @param string $service
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        StorageInterface $storage,
        CredentialsInterface $credentials,
        ConfigInterface $config,
        $service
    ) {
        $this->objectManager = $objectManager;
        $this->storage = $storage;
        $this->credentials = $credentials;
        $this->config = $config;
        $this->service = $service;
    }
}
