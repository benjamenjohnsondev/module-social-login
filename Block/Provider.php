<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Block;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use BenJohnsonDev\SocialLogin\Controller\Account\Register;
use Magento\Framework\View\Element\Template;

class Provider extends Template
{
    public const TEMPLATE_PATH = "BenJohnsonDev_SocialLogin::login/form/provider.phtml";

    /**
     * Get form action URL for POST request
     *
     * @return string
     */
    public function getPostActionUrl(): string
    {
        return $this->_urlBuilder->getUrl(
            Register::ROUTE,
            ['_secure' => true]
        );
    }

    /**
     * Gets provider
     *
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface
    {
        return $this->getData('provider');
    }

}
