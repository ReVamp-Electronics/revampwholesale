<?php
namespace Aheadworks\SocialLogin\Test\Block\ProviderLoginForm;

use Aheadworks\SocialLogin\Test\Block\ProviderLoginForm;

/**
 * Class Linkedin
 */
class Linkedin extends ProviderLoginForm
{
    /**
     * @var string
     */
    protected $submitButtonSelector = 'input[name="authorize"]';
}
