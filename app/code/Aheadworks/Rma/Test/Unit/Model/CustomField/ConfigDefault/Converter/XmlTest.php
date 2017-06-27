<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\CustomField\ConfigDefault\Converter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter\Xml|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = $this->getMockBuilder('Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter\Xml')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    /**
     * @dataProvider documentProvider
     */
    public function testConvert($document)
    {
        $result = $this->converter->convert($document);
        $this->assertTrue(is_array($result));
    }

    public function documentProvider()
    {
        $document = new \DOMDocument();
        return [[$document]];
    }
}
