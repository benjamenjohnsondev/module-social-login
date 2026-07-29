<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Block\Form;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\View\Element\Template;

class Edit extends Template
{
    private ?ProviderInterface $providerCache = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \BenJohnsonDev\SocialLogin\Api\ProviderRepositoryInterface $providerRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private readonly Session $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProviderRepositoryInterface $providerRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Add a body class when the customer is using social login so CSS can hide the password fields immediately.
     *
     * @return \BenJohnsonDev\SocialLogin\Block\Form\Edit
     */
    protected function _prepareLayout(): Edit
    {
        try {
            if ($this->isCustomerSocialLogin()) {
                $this->pageConfig->addBodyClass('social-login-active');
            }
        } catch (\Exception) {
            // Session not available — fail silently
        }

        return parent::_prepareLayout();
    }

    /**
     * Get the current customer data object
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    /**
     * Return whether the account-edit form should open in change-password mode
     *
     * @return bool
     */
    public function getChangePassword(): bool
    {
        return (bool) $this->customerSession->getChangePassword();
    }

    /**
     * Get the provider attribute for the current customer
     *
     * @return \Magento\Framework\Api\AttributeInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderAttribute(): ?AttributeInterface
    {
        return $this->getCustomer()->getCustomAttribute('provider');
    }

    /**
     * Check if the current customer authenticates via social login
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCustomerSocialLogin(): bool
    {
        $attr = $this->getProviderAttribute();
        return $attr !== null && $attr->getValue() !== null && $attr->getValue() !== 'revoked';
    }

    /**
     * Get the provider model for the current customer (memoised)
     *
     * @return \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProvider(): ProviderInterface
    {
        if ($this->providerCache === null) {
            $this->providerCache = $this->providerRepository->getByCode(
                $this->getProviderAttribute()->getValue()
            );
        }
        return $this->providerCache;
    }

    /**
     * Get the provider code for the current customer
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderCode(): string
    {
        return $this->getProvider()->getCode();
    }

    /**
     * Get the provider label for the current customer
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderLabel(): string
    {
        return $this->getProvider()->getLabel();
    }

    /**
     * Social login customers have no usable password — suppress the password field
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isPasswordRequired(): bool
    {
        return !$this->isCustomerSocialLogin();
    }

    /**
     * Social login customers have no usable current password — suppress the current-password field
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCurrentPasswordRequired(): bool
    {
        return !$this->isCustomerSocialLogin();
    }
}
