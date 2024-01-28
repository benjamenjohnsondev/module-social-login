<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Block\Form;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Model\ProviderRepository;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;

class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \BenJohnsonDev\SocialLogin\Model\ProviderRepository $providerRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        protected ProviderRepository $providerRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement
        );
    }

    /**
     * Get the provider label
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderLabel(): string
    {

        return $this->getProvider()->getLabel();
    }

    /**
     * Get the provider
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProvider(): ProviderInterface
    {
        return $this->providerRepository->getByCode($this->getProviderAttribute()->getValue());
    }

    /**
     * Get the provider attribute
     *
     * @return \Magento\Framework\Api\AttributeInterface
     */
    public function getProviderAttribute(): AttributeInterface
    {
        return $this->getCustomer()->getCustomAttribute('provider');
    }

    /**
     * Check if the customer is using social login
     *
     * @return bool
     */
    public function isCustomerSocialLogin(): bool
    {
        return $this->getProviderAttribute() !== null;
    }
}
