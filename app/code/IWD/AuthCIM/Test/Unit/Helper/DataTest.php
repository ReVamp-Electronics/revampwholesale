<?php

namespace IWD\AuthoCIM\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \IWD\AuthCIM\Helper\Data
     */
    private $dataHelper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataHelper = $helper->getObject('IWD\AuthCIM\Helper\Data');
    }

    /**
     * @param string $code
     * @param string $expected
     *
     * @dataProvider getCreditCardTypeDataProvider
     */
    public function testGetCreditCardType($code, $expected)
    {
        $this->assertSame($expected, (string)$this->dataHelper->getCreditCardType($code));
    }

    /**
     * @return array
     */
    public function getCreditCardTypeDataProvider()
    {
        return [
            ['AE', 'American Express'],
            ['VI', 'Visa'],
            ['MC', 'MasterCard'],
            ['DI', 'Discover'],
            ['MI', 'Maestro'],
            ['JBC', 'JBC'],
            ['CUP', 'China Union Pay']
        ];
    }

    /**
     * @param string $type
     * @param string $expected
     *
     * @dataProvider getCreditCardTypeCodeDataProvider
     */
    public function testGetCreditCardTypeCode($type, $expected)
    {
        $this->assertSame($expected, (string)$this->dataHelper->getCreditCardTypeCode($type));
    }

    /**
     * @return array
     */
    public function getCreditCardTypeCodeDataProvider()
    {
        return [
            ['American Express', 'AE'],
            ['AmericanExpress', 'AE'],
            ['Visa', 'VI'],
            ['MasterCard', 'MC'],
            ['Discover', 'DI'],
            ['Maestro', 'MI'],
            ['JBC', 'JBC'],
            ['China Union Pay', 'CUP'],
            ['ChinaUnionPay', 'CUP']
        ];
    }
}
