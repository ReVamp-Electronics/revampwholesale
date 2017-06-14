<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;

/**
 * Class Odnoklassniki.
 */
class Odnoklassniki extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_ACCOUNT_GET = 'users/getCurrentUser';

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Odnoklassniki $service */
        $response = $service->requestWithParams(self::API_METHOD_ACCOUNT_GET, []);

        $responseData = $this->decodeJson($response);
        return $this->createDataObject()->setData($responseData);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponseData(\Magento\Framework\DataObject $responseData)
    {
        return [
            AccountInterface::TYPE => AccountInterface::TYPE_ODNOKLASSNIKI,
            AccountInterface::SOCIAL_ID => $responseData->getData('uid'),
            AccountInterface::FIRST_NAME => $responseData->getData('first_name'),
            AccountInterface::LAST_NAME => $responseData->getData('last_name'),
            AccountInterface::IMAGE_URL => $responseData->getData('pic_1')
        ];
    }
}
