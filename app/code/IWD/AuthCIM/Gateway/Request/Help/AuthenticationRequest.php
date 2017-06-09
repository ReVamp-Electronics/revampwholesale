<?php

namespace IWD\AuthCIM\Gateway\Request\Help;

use IWD\AuthCIM\Gateway\Config\Config;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AuthenticationRequest
 * @package IWD\AuthCIM\Gateway\Request\Help
 */
class AuthenticationRequest implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            'root' => 'authenticateTestRequest',
            'merchantAuthentication' => [
                'name' => $this->config->getApiLoginId(),
                'transactionKey' => $this->config->getTransKey()
            ]
        ];
    }
}
