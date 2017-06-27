<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

class Vk extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_USERS_GET = 'users.get';

    /**
     * @var array
     */
    private $requestParams = [
        'fields' => 'photo_50'
    ];

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Vk $service */
        $response = $service->requestWithParams(self::API_METHOD_USERS_GET, $this->requestParams);
        $responseData = $this->decodeJson($response);

        return $this->createDataObject()->setData($responseData);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponseData(\Magento\Framework\DataObject $responseData)
    {
        return [
            AccountInterface::TYPE => AccountInterface::TYPE_VK,
            AccountInterface::SOCIAL_ID => $responseData->getData('response/0/uid'),
            AccountInterface::FIRST_NAME => $responseData->getData('response/0/first_name'),
            AccountInterface::LAST_NAME => $responseData->getData('response/0/last_name'),
            AccountInterface::IMAGE_URL => $responseData->getData('response/0/photo_50')
        ];
    }
}
