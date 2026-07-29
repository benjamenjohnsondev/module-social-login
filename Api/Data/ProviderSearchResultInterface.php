<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ProviderSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get Providers
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface[]
     */
    public function getItems();

    /**
     * Set Providers
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
