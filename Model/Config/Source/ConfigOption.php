<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Config\Source;

use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ConfigOption implements OptionSourceInterface
{
    /**
     * @param \BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface $providerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        protected ProviderRepositoryInterface $providerRepository,
        protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
    ) {
    }

    /**
     * Return provider options
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()->create();
        $providers = $this->providerRepository->getList($searchCriteria)->getItems();

        $return = [];
        foreach ($providers as $provider) {
            $return[] = [
                'value' => $provider->getCode(),
                'label' => $provider->getLabel(),
            ];
        }
        return $return;
    }
}
