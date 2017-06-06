<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Helper;

/**
 * Class FileTest
 * @package Aheadworks\Rma\Test\Unit\Helper
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Helper\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileHelper;

    protected function setUp()
    {
        $this->fileHelper = $this->getMockBuilder('Aheadworks\Rma\Helper\File')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    /**
     * @dataProvider fileSizeDataProvider
     */
    public function testGetTextFileSize($fileSize, $textFileSize)
    {
        $this->assertEquals($textFileSize, $this->fileHelper->getTextFileSize($fileSize));
    }

    /**
     * @return array
     */
    public function fileSizeDataProvider()
    {
        return [
            [1024, '1024b'],
            [2048, '2048b'],
            [2049, '2kb'],
            [1048576, '1024kb'],
            [2097152, '2048kb'],
            [2097153, '2mb'],
        ];
    }
}
