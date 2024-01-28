<?php

declare (strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterface;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterfaceFactory;
use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\CollectionFactory as ProviderCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ProviderRepository implements ProviderRepositoryInterface
{
    public const SOCIAL_LOGIN_GENERAL_ENABLED_CONFIG_PATH = 'social_login/general/enabled';
    public const SOCIAL_LOGIN_GENERAL_PROVIDERS_CONFIG_PATH = 'social_login/general/enabled_providers';

    /**
     * @param \BenJohnsonDev\SocialLogin\Model\ProviderFactory $providerFactory
     * @param \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\CollectionFactory $providerCollectionFactory
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterfaceFactory $searchResultFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider $providerResourceModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ProviderFactory $providerFactory,
        protected ProviderCollectionFactory $providerCollectionFactory,
        protected ProviderSearchResultInterfaceFactory $searchResultFactory,
        protected CollectionProcessorInterface $collectionProcessor,
        protected ResourceModel\Provider $providerResourceModel,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Delete provider
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return void
     * @throws \Exception
     */
    public function delete(ProviderInterface $provider): void
    {
        /** @var $provider Provider * */
        $this->providerResourceModel->delete($provider);
    }

    /**
     * Get all providers from table
     *
     * @return ProviderInterface[]
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function getAllProviders()
    {
        $collection = $this->providerCollectionFactory->create();
        return $collection->getItems();
    }

    /**
     * Get provider by id
     *
     * @param string $code
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode(string $code): ProviderInterface
    {
        $provider = $this->providerFactory->create();
        $this->providerResourceModel->load($provider, $code, ProviderInterface::CODE);
        if (!$provider->getId()) {
            throw new NoSuchEntityException(__('Unable to find Provider with code "%1"', $code));
        }
        return $provider;
    }

    /**
     * Get provider by code
     *
     * @param int $id
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): ProviderInterface
    {
        $provider = $this->providerFactory->create();
        $this->providerResourceModel->load($provider, $id);
        if (!$provider->getId()) {
            throw new NoSuchEntityException(__('Unable to find Provider with ID "%1"', $id));
        }
        return $provider;
    }

    /**
     * Gets all valid providers
     *
     * @return ProviderInterface[]
     */
    public function getEnabledProviders(): array
    {
        if (!$this->scopeConfig->isSetFlag(
            self::SOCIAL_LOGIN_GENERAL_ENABLED_CONFIG_PATH
        )) {
            return [];
        }

        $enabledProviders = $this->scopeConfig->getValue(
            self::SOCIAL_LOGIN_GENERAL_PROVIDERS_CONFIG_PATH
        );

        /** @var \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\Collection $collection */
        $collection = $this->providerCollectionFactory->create();
        $collection->addFieldToFilter(
            'code',
            [
                ['in' => $enabledProviders],
            ],
        )->addFieldToFilter(
            'oauth_class',
            [
                ['neq' => ''],
            ],
        )->addFieldToFilter(
            'oauth_class',
            [
                ['neq' => ''],
            ],
        );
        return $collection->getItems();
    }

    /**
     * Get list of providers
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ProviderSearchResultInterface
    {
        $collection = $this->providerCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        /** @noinspection PhpParamsInspection */
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Save provider
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(ProviderInterface $provider): void
    {
        /** @var $provider Provider * */
        $this->providerResourceModel->save($provider);
    }
}
