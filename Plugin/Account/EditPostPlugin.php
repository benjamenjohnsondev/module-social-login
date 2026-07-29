<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Plugin\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\EditPost;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;

class EditPostPlugin
{
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly Session $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory $redirectFactory,
    ) {
    }

    /**
     * After the core account edit save, process an unlink request if present.
     *
     * @param \Magento\Customer\Controller\Account\EditPost $subject
     * @param \Magento\Framework\Controller\ResultInterface $result
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(EditPost $subject, ResultInterface $result): ResultInterface
    {
        $providerCode = $this->request->getPostValue('unlink_provider');

        if (!$providerCode) {
            return $result;
        }

        $customer = $this->customerSession->getCustomerData();

        $providerAttr = $customer->getCustomAttribute('provider');
        if ($providerAttr === null || $providerAttr->getValue() !== $providerCode) {
            $this->messageManager->addErrorMessage(__('Unable to unlink account: provider mismatch.'));
            return $result;
        }

        foreach (['provider', 'social_uid', 'refresh_token', 'token_expire'] as $attr) {
            $customer->setCustomAttribute($attr, null);
        }

        $this->customerRepository->save($customer);

        $this->messageManager->addSuccessMessage(
            __('Your %1 account has been unlinked. You can now set a password to log in.', $providerCode)
        );

        // Log the customer out — they have no password yet, force them to go through forgot-password
        $this->customerSession->logout();
        $this->customerSession->start();

        return $this->redirectFactory->create()->setPath('customer/account/login');
    }
}
