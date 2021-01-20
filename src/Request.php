<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use InvalidArgumentException;
use function getenv;
use function is_string;
use function sprintf;

/**
 * Get the domain and validation string from environment variables as passed by Certbot's manual validation hooks.
 *
 * @see https://certbot.eff.org/docs/using.html#pre-and-post-validation-hooks
 */
final class Request
{
    private const DOMAIN = 'CERTBOT_DOMAIN';
    private const VALIDATION = 'CERTBOT_VALIDATION';

    /**
     * The domain being authenticated.
     *
     * @var string
     */
    private $domain;

    /**
     * The validation string.
     *
     * @var string
     */
    private $validation;

    /**
     * @param mixed $domain     the domain being authenticated
     * @param mixed $validation the validation string
     */
    public function __construct($domain, $validation)
    {
        $this->assertNonEmptyString(self::DOMAIN, $domain);
        $this->assertNonEmptyString(self::VALIDATION, $validation);

        $this->domain = $domain;
        $this->validation = $validation;
    }

    /**
     * Get domain and validation string from the environment.
     */
    public static function fromEnv(): self
    {
        return new self(
            getenv(self::DOMAIN),
            getenv(self::VALIDATION),
        );
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getValidation(): string
    {
        return $this->validation;
    }

    /**
     * @param mixed $value
     */
    private function assertNonEmptyString(string $envVar, $value): void
    {
        if (empty($value) || !is_string($value)) {
            throw new InvalidArgumentException(sprintf('Environment variable "%s" must be set and contain a non-empty value', $envVar));
        }
    }
}
