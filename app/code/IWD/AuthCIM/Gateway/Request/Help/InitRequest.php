<?php

namespace IWD\AuthCIM\Gateway\Request\Help;

use IWD\AuthCIM\Gateway\Request\AbstractRequest;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class InitRequest
 * @package IWD\AuthCIM\Gateway\Request\Help
 */
class InitRequest extends AbstractRequest implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $this->setBuildSubject($buildSubject);

        return [
            'merchantAuthentication' => $this->getMerchantAuthentication(),
            'refId' => 'A1000127'
        ];
    }
}
