<?php

namespace IWD\AuthCIM\Test\Unit\Gateway\Config;

use IWD\AuthCIM\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const METHOD_CODE = 'iwd_authcim';
    const STORE_ID = 1;

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMock(ScopeConfigInterface::class);

        $this->initStoreMock();
        $this->model = new Config($this->scopeConfigMock, $this->storeManager);
    }

    /**
     * Create mock object for store
     */
    private function initStoreMock()
    {
        $this->store = $this->getMock(StoreInterface::class);
        $this->store->expects(static::any())
            ->method('getId')
            ->willReturn(self::STORE_ID);

        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->storeManager->expects(static::any())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
    }

    /**
     * @covers \IWD\AuthCIM\Gateway\Config\Config::isCvvEnabled
     */
    public function testUseCvv()
    {
        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_USE_CVV), ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        static::assertEquals(true, $this->model->isCvvEnabled());
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, self::METHOD_CODE, $field);
    }
}
