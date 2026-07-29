<?php

declare(strict_types=1);

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
        protected ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function delete(ProviderInterface $provider): void
    {
        $this->providerResourceModel->delete($provider);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getEnabledProviders(): array
    {
        if (!$this->scopeConfig->isSetFlag(self::SOCIAL_LOGIN_GENERAL_ENABLED_CONFIG_PATH)) {
            return [];
        }

        $enabledProviders = $this->scopeConfig->getValue(self::SOCIAL_LOGIN_GENERAL_PROVIDERS_CONFIG_PATH);

        /** @var \BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider\Collection $collection */
        $collection = $this->providerCollectionFactory->create();
        $collection->addFieldToFilter('code', [['in' => $enabledProviders]])
            ->addFieldToFilter('oauth_class', [['neq' => '']]);

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ProviderSearchResultInterface
    {
        $collection = $this->providerCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function save(ProviderInterface $provider): ProviderInterface
    {
        $this->providerResourceModel->save($provider);
        return $provider;
    }
}
