<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Test\Unit\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Model\Account\LoginManagement;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginManagementTest extends TestCase
{
    private LoginManagement $subject;
    private Session&MockObject $customerSession;
    private RedirectFactory&MockObject $redirectFactory;
    private UrlInterface&MockObject $urlBuilder;
    private ManagerInterface&MockObject $messageManager;

    protected function setUp(): void
    {
        $this->customerSession = $this->createMock(Session::class);
        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->subject = new LoginManagement(
            $this->customerSession,
            $this->redirectFactory,
            $this->urlBuilder,
            $this->messageManager,
        );
    }

    public function testLoginCustomerSuccess(): void
    {
        $customer = $this->buildCustomer('google');
        $accessToken = $this->buildAccessToken(time() + 3600);
        $provider = $this->buildProvider('google');
        $redirect = $this->createMock(Redirect::class);

        $this->redirectFactory->method('create')->willReturn($redirect);
        $this->urlBuilder->method('getUrl')->willReturn('https://example.com/customer/account');
        $redirect->method('setPath')->willReturnSelf();

        $this->customerSession->expects($this->once())->method('setCustomerDataAsLoggedIn');
        $this->customerSession->expects($this->once())->method('regenerateId');
        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $result = $this->subject->loginCustomer($customer, $accessToken, $provider);

        $this->assertSame($redirect, $result);
    }

    public function testLoginCustomerThrowsOnExpiredToken(): void
    {
        $customer = $this->buildCustomer('google');
        $accessToken = $this->buildAccessToken(time() - 1);
        $provider = $this->buildProvider('google');

        $this->expectException(InputMismatchException::class);
        $this->subject->loginCustomer($customer, $accessToken, $provider);
    }

    public function testLoginCustomerThrowsOnProviderMismatch(): void
    {
        $customer = $this->buildCustomer('facebook');
        $accessToken = $this->buildAccessToken(time() + 3600);
        $provider = $this->buildProvider('google');

        $this->expectException(InputMismatchException::class);
        $this->subject->loginCustomer($customer, $accessToken, $provider);
    }

    public function testLoginCustomerThrowsWhenProviderAttributeNull(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with('provider')->willReturn(null);
        $accessToken = $this->buildAccessToken(time() + 3600);
        $provider = $this->buildProvider('google');

        $this->expectException(InputMismatchException::class);
        $this->subject->loginCustomer($customer, $accessToken, $provider);
    }

    private function buildCustomer(string $providerCode): CustomerInterface&MockObject
    {
        $attr = $this->createMock(AttributeInterface::class);
        $attr->method('getValue')->willReturn($providerCode);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with('provider')->willReturn($attr);
        return $customer;
    }

    private function buildAccessToken(int $expires): AccessTokenInterface&MockObject
    {
        $token = $this->createMock(AccessTokenInterface::class);
        $token->method('getExpires')->willReturn($expires);
        return $token;
    }

    private function buildProvider(string $code): ProviderInterface&MockObject
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('getCode')->willReturn($code);
        return $provider;
    }
}
