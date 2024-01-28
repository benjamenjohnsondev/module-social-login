<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\SecurityChecker\Frequency;
use Magento\Security\Model\SecurityChecker\Quantity;

class BypassChecker
{
    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * Check if reset password requests should be bypassed
     *
     * This will only fire for social login requests - since we use the reset password functionality each time the user logs in
     *
     * @param Frequency|Quantity $subject
     * @param callable $proceed
     * @param int $securityEventType
     * @param string|null $accountReference
     * @param string|null $longIp
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\SecurityViolationException
     * @noinspection PluginInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUnused
     */
    public function aroundCheck(
        $subject,
        callable $proceed,
        int $securityEventType,
        string $accountReference = null,
        string $longIp = null
    ): void {
        try {
            $proceed($securityEventType, $accountReference, $longIp);
        } catch (SecurityViolationException $e) {
            $customer = $this->customerRepository->get($accountReference);

            // Check for the provider attribute to determine if this is a social login request
            if ($customer->getCustomAttribute('provider')->getValue() !== null ||
                $customer->getCustomAttribute('provider')->getValue() !== 'revoked'
            ) {
                return;
            }

            // If the customer is not a social login customer, re-throw the exception
            throw new SecurityViolationException(__($e->getMessage()));
        }
    }
}
