<?php
namespace Aheadworks\SocialLogin\Test\Constraint\LoginBlock;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Page\FrontendPage;

/**
 * Class AssertVisible
 */
abstract class AssertVisible extends AbstractConstraint
{
    /**
     * Default login block name
     */
    const DEFAULT_BLOCK_NAME = 'socialLoginBlock';

    /**
     * @var string
     */
    protected $blockName = self::DEFAULT_BLOCK_NAME;

    /**
     * Process is visible block assert
     *
     * @param FrontendPage $loginPage
     */
    protected function processVisibleAssert(FrontendPage $loginPage)
    {
        $loginPage->open();
        $isBlockVisible = $this->getPageBlock($loginPage)->isVisible();

        \PHPUnit_Framework_Assert::assertTrue(
            $isBlockVisible,
            'Social account doesn\'t visible.'
        );
    }

    /**
     * @param FrontendPage $page
     * @return \Magento\Mtf\Block\BlockInterface
     */
    protected function getPageBlock(FrontendPage $page)
    {
        return $page->getBlockInstance($this->blockName);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Social account doesn\'t visible on.';
    }
}
