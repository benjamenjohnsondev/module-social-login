<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ProviderRepositoryInterface
{
    /**
     * Delete provider
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return void
     */
    public function delete(ProviderInterface $provider): void;

    /**
     * Get provider by code
     *
     * @param string $code
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode(string $code): ProviderInterface;

    /**
     * Get enabled providers
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface[]
     */
    public function getEnabledProviders(): array;

    /**
     * Get provider by id
     *
     * @param int $id
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): ProviderInterface;

    /**
     * Get list of providers
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ProviderSearchResultInterface;

    /**
     * Save provider
     *
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(ProviderInterface $provider): ProviderInterface;
}
