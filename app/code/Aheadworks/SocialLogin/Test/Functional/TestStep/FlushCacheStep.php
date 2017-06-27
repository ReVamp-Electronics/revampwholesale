<?php
namespace Aheadworks\SocialLogin\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class FlushCacheStep
 */
class FlushCacheStep implements TestStepInterface
{
    /**
     * @var AdminCache
     */
    protected $adminCache;

    /**
     * @param AdminCache $adminCache
     */
    public function __construct(
        AdminCache $adminCache
    ) {
        $this->adminCache = $adminCache;
    }

    /**
     * Flush cache
     *
     * @return void
     */
    public function run()
    {
        // Flush cache
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();
    }
}
