<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Magento\Framework\DataObject;

/**
 * Class Pinterest.
 */
class Pinterest extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_ACCOUNT_GET = 'v1/me/?fields=id,first_name,last_name,image';

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Pinterest $service */
        $response = $service->request(self::API_METHOD_ACCOUNT_GET);

        $responseData = $this->decodeJson($response);
        return $this->createDataObject()->setData($responseData);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponseData(DataObject $responseData)
    {
        return [
            AccountInterface::TYPE => AccountInterface::TYPE_PINTEREST,
            AccountInterface::SOCIAL_ID => $responseData->getData('data/id'),
            AccountInterface::FIRST_NAME => $responseData->getData('data/first_name'),
            AccountInterface::LAST_NAME => $responseData->getData('data/last_name'),
            AccountInterface::IMAGE_URL => $this->getImageUrl($responseData)
        ];
    }

    /**
     * Get image url.
     *
     * @param DataObject $responseData
     * @return string|null
     */
    private function getImageUrl(DataObject $responseData)
    {
        $images = $responseData->getData('data/image');
        if (!is_array($images)) {
            return null;
        }

        $image = current($images);

        return isset($image['url']) ? $image['url'] : null;
    }
}
