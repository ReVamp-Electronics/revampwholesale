<?php

namespace IWD\AuthCIM\Block\Acceptjs;

use IWD\AuthCIM\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\View\Element\Template;

/**
 * Class Js
 * @package IWD\AuthCIM\Block\Acceptjs
 */
class Js extends Template
{
    /**
     * @var GatewayConfig
     */
    private $config;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param GatewayConfig $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        GatewayConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getAcceptJsUrl()
    {
        return $this->config->getAcceptJsUrl();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->config->isActive() && $this->config->isAcceptJsEnabled();
    }
}
