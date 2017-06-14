<?php
namespace Aheadworks\SocialLogin\Model\LoginBlock;

/**
 * Class Template
 */
class Template
{
    /**
     * Template path
     *
     * @var string
     */
    protected $path;

    /**
     * Additional data
     *
     * @var array
     */
    protected $additionalData;

    /**
     * @param string $path
     * @param array $additionalData
     */
    public function __construct(
        $path,
        array $additionalData = []
    ) {
        $this->path = $path;
        $this->additionalData = $additionalData;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get additional data
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
