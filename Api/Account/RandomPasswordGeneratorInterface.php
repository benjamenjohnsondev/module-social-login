<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

interface RandomPasswordGeneratorInterface
{
    /**
     * Gets a random password
     *
     * Conforms to Magento's password requirements.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate(): string;
}
