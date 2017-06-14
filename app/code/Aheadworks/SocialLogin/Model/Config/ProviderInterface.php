<?php
namespace Aheadworks\SocialLogin\Model\Config;

/**
 * Interface ProviderInterface
 */
interface ProviderInterface
{
    /**
     * Is provider enabled
     *
     * @return array
     */
    public function isEnabled();

    /**
     * Get provider code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get provider title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();
}
