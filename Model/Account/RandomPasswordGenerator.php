<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\RandomPasswordGeneratorInterface;
use Magento\Framework\Math\Random;

class RandomPasswordGenerator implements RandomPasswordGeneratorInterface
{
    public const CHARS_SPECIALS = '!@#$%^&*()_-=+{}[];:<>?/|';

    /**
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        protected Random $mathRandom,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function generate(): string
    {
        return $this->mathRandom->getRandomString(
            30,
            Random::CHARS_LOWERS . Random::CHARS_UPPERS . Random::CHARS_DIGITS . self::CHARS_SPECIALS
        );
    }
}
