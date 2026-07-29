<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Test\Unit\Plugin;

use BenJohnsonDev\SocialLogin\Plugin\BypassChecker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Security\Model\SecurityChecker\Frequency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BypassCheckerTest extends TestCase
{
    private BypassChecker $subject;
    private CustomerRepositoryInterface&MockObject $customerRepository;

    protected function setUp(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->subject = new BypassChecker($this->customerRepository);
    }

    public function testProceedsNormallyWhenNoException(): void
    {
        $subject = $this->createMock(Frequency::class);
        $proceed = fn() => null;

        // No exception — just passes through
        $this->subject->aroundCheck($subject, $proceed, 1, 'user@example.com', null);
        $this->addToAssertionCount(1);
    }

    public function testBypassesRateLimitForSocialLoginCustomer(): void
    {
        $subject = $this->createMock(Frequency::class);
        $exception = new SecurityViolationException(__('Too many attempts'));

        $proceed = function () use ($exception) {
            throw $exception;
        };

        $customer = $this->buildCustomer('google');
        $this->customerRepository->method('get')->with('social@example.com')->willReturn($customer);

        // Should NOT rethrow — returns void silently
        $this->subject->aroundCheck($subject, $proceed, 1, 'social@example.com', null);
        $this->addToAssertionCount(1);
    }

    public function testRethrowsForNonSocialCustomer(): void
    {
        $subject = $this->createMock(Frequency::class);
        $exception = new SecurityViolationException(__('Too many attempts'));

        $proceed = function () use ($exception) {
            throw $exception;
        };

        $customer = $this->buildCustomer(null);
        $this->customerRepository->method('get')->with('normal@example.com')->willReturn($customer);

        $this->expectException(SecurityViolationException::class);
        $this->subject->aroundCheck($subject, $proceed, 1, 'normal@example.com', null);
    }

    public function testRethrowsForRevokedCustomer(): void
    {
        $subject = $this->createMock(Frequency::class);
        $exception = new SecurityViolationException(__('Too many attempts'));

        $proceed = function () use ($exception) {
            throw $exception;
        };

        $customer = $this->buildCustomer('revoked');
        $this->customerRepository->method('get')->with('revoked@example.com')->willReturn($customer);

        $this->expectException(SecurityViolationException::class);
        $this->subject->aroundCheck($subject, $proceed, 1, 'revoked@example.com', null);
    }

    private function buildCustomer(?string $providerValue): CustomerInterface&MockObject
    {
        $customer = $this->createMock(CustomerInterface::class);

        if ($providerValue === null) {
            $customer->method('getCustomAttribute')->with('provider')->willReturn(null);
        } else {
            $attr = $this->createMock(AttributeInterface::class);
            $attr->method('getValue')->willReturn($providerValue);
            $customer->method('getCustomAttribute')->with('provider')->willReturn($attr);
        }

        return $customer;
    }
}
