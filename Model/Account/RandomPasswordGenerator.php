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
        protected Random $mathRandom
    ) {
    }

    /**
     * @inheritDoc
     */
    public function generate(): string
    {
        $string = $this->mathRandom->getRandomString(
            255,
            Random::CHARS_LOWERS . Random::CHARS_UPPERS . self::CHARS_SPECIALS
        );
        $numbers = $this->mathRandom->getRandomString(100, Random::CHARS_DIGITS);

        $shuffled = str_shuffle($string . $numbers);

        return substr($shuffled, 0, 30);
    }
}
