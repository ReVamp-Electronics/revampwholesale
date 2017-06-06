<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Test\Unit\Model\ResourceModel;

class CustomFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aheadworks\Rma\Model\ResourceModel\CustomField|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->getMockBuilder('Aheadworks\Rma\Model\ResourceModel\CustomField')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
    }

    public function testGetValidationRulesBeforeSave()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Validator\DataObject',
            $this->model->getValidationRulesBeforeSave(),
            "'getValidationRulesBeforeSave()' should return instance of 'Magento\\Framework\\Validator\\DataObject'."
        );
    }
}
