<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

class Twitter extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_ACCOUNT_GET = 'account/verify_credentials.json';

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
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
            AccountInterface::TYPE => AccountInterface::TYPE_TWITTER,
            AccountInterface::SOCIAL_ID => $responseData->getData('id'),
            AccountInterface::FIRST_NAME => $responseData->getData('name'),
            AccountInterface::IMAGE_URL => $responseData->getData('profile_image_url')
        ];
    }
}
