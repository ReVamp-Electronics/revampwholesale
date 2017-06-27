<?php
namespace Aheadworks\SocialLogin\Model\Config;

/**
 * Class Config
 */
class General extends AbstractConfig
{
    const XML_PATH_ENABLED = 'social/general/enabled';

    /**
     * Is module enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->isSetFlag(self::XML_PATH_ENABLED);
    }
}
