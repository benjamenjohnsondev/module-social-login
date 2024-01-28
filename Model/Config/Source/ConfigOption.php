<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Config\Source;

use BenJohnsonDev\SocialLogin\Model\ProviderRepository;
use Magento\Framework\Data\OptionSourceInterface;

class ConfigOption implements OptionSourceInterface
{
    /**
     * @param \BenJohnsonDev\SocialLogin\Model\ProviderRepository $providerRepository
     */
    public function __construct(
        protected ProviderRepository $providerRepository
    ) {
    }

    /**
     * Return provider options
     *
     * @return array[]
     */
    public function toOptionArray(): array
    {
        foreach ($this->getAllOptions() as $option) {
            $return[] = [
                'value' => $option->getCode(),
                'label' => $option->getLabel(),
            ];
        }
        return $return ?? [];
    }

    /**
     * Get all providers from table
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface[]
     */
    public function getAllOptions(): array
    {
        return $this->providerRepository->getAllProviders();
    }
}
