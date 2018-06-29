<?php
/**
 * Copyright © 2018 OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\Gdpr\Service\Anonymize;

use Magento\Framework\Math\Random;
use Magento\Framework\Phrase;

/**
 * Class AnonymizeTool
 */
class AnonymizeTool
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * AnonymizeTool constructor.
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        Random $mathRandom
    ) {
        $this->mathRandom = $mathRandom;
    }

    /**
     * Retrieve an anonymous value
     *
     * @return string
     */
    public function anonymousValue(): string
    {
        return (new Phrase('Anonymous'))->render();
    }

    /**
     * Retrieve an anonymous email
     *
     * @return string
     */
    public function anonymousEmail(): string
    {
        return (new Phrase('anonymous@gdpr.com'))->render();
    }

    /**
     * Retrieve a random value
     *
     * @param int $length
     * @param null|string $chars
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function randomValue(int $length = 10, ?string $chars = null): string
    {
        return $this->mathRandom->getRandomString($length, $chars);
    }
}