<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

class LinkedIn extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_ACCOUNT_GET = '/people/~:(id,first-name,last-name,picture-url,email-address)?format=json';

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\LinkedIn $service */
        $response = $service->request(self::API_METHOD_ACCOUNT_GET);
        $responseData = $this->decodeJson($response);
        return $this->createDataObject()->setData($responseData);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponseData(\Magento\Framework\DataObject $responseData)
    {
        return [
            AccountInterface::TYPE => AccountInterface::TYPE_LINKED_IN,
            AccountInterface::SOCIAL_ID => $responseData->getData('id'),
            AccountInterface::FIRST_NAME => $responseData->getData('firstName'),
            AccountInterface::LAST_NAME => $responseData->getData('lastName'),
            AccountInterface::IMAGE_URL => $responseData->getData('pictureUrl'),
            AccountInterface::EMAIL => $responseData->getData('emailAddress')
        ];
    }
}
