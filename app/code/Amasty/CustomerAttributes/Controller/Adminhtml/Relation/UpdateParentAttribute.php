<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Controller\Adminhtml\Relation;

class UpdateParentAttribute extends \Amasty\CustomerAttributes\Controller\Adminhtml\Relation
{
    /**
     * @var \Amasty\CustomerAttributes\Model\Relation\AttributeOptionsProvider
     */
    private $optionsProvider;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;
    /**
     * @var \Amasty\CustomerAttributes\Model\Relation\DependentAttributeProvider
     */
    private $attributeProvider;

    /**
     * UpdateParentAttribute constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                  $context
     * @param \Magento\Framework\Registry                                          $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory                           $resultPageFactory
     * @param \Amasty\CustomerAttributes\Api\RelationRepositoryInterface           $relationRepository
     * @param \Amasty\CustomerAttributes\Model\RelationFactory                     $relationFactory
     * @param \Magento\Framework\Json\EncoderInterface                             $jsonEncoder
     * @param \Amasty\CustomerAttributes\Model\Relation\AttributeOptionsProvider   $optionsProvider
     * @param \Amasty\CustomerAttributes\Model\Relation\DependentAttributeProvider $attributeProvider
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\CustomerAttributes\Api\RelationRepositoryInterface $relationRepository,
        \Amasty\CustomerAttributes\Model\RelationFactory $relationFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\CustomerAttributes\Model\Relation\AttributeOptionsProvider $optionsProvider,
        \Amasty\CustomerAttributes\Model\Relation\DependentAttributeProvider $attributeProvider
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $relationRepository, $relationFactory);
        $this->jsonEncoder = $jsonEncoder;
        $this->optionsProvider = $optionsProvider;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * For Ajax
     *
     * @return \Magento\Framework\App\Response\Http with JSON
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $response = [
            'error' => __('The attribute_id is not defined. Please try to reload the page. ')
        ];
        if ($attributeId) {
            try {
                $attributeOptions = $this->optionsProvider->setParentAttributeId($attributeId)->toOptionArray();
                $dependentAttributes = $this->attributeProvider->setExcludeAttributeId($attributeId)->toOptionArray();
                $response = [
                    'attribute_options' => $attributeOptions,
                    'dependent_attributes' => $dependentAttributes,
                    'error' => 0
                ];
            } catch (\Exception $exception) {
                $response = [
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->getResponse()->representJson(
            $this->jsonEncoder->encode($response)
        );
    }
}
