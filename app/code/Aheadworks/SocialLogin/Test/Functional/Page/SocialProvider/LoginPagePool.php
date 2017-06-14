<?php
namespace Aheadworks\SocialLogin\Test\Page\SocialProvider;

use Magento\Mtf\Page\FrontendPage;

/**
 * Class LoginPagePool
 */
class LoginPagePool
{
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @param array $pages
     */
    public function __construct(
        array $pages = []
    ) {
        $this->pages = $pages;
    }

    /**
     * Get page by provider
     *
     * @param $providerName
     * @return FrontendPage
     */
    public function getPage($providerName)
    {
        if (!isset($this->pages[$providerName])) {
            return null;
        }
        return $this->pages[$providerName];
    }
}
