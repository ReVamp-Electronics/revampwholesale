<?php

namespace IWD\AuthCIM\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use IWD\AuthCIM\Gateway\Config\Config;

/**
 * Class TransferFactory
 * @package IWD\AuthCIM\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * TransferFactory constructor.
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $uri = $this->config->getGatewayUrl();

        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->setUri($uri)
            ->build();
    }
}
