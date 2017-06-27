<?php
namespace Aheadworks\SocialLogin\Test\Block;

use Magento\Mtf\Block\Form;

/**
 * Class ProviderLoginForm
 */
abstract class ProviderLoginForm extends Form
{
    /**
     * @var string
     */
    protected $submitButtonSelector = '';

    /**
     * Fill credentials data
     *
     * @param array $data
     * @throws \Exception
     */
    public function fillCredentials($data)
    {
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping);
    }
    /**
     * Click allow
     */
    public function clickAllow()
    {
        $this->_rootElement->find($this->submitButtonSelector)->click();
    }
}
