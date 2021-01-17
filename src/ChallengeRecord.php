<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use function rtrim;
use function sprintf;

/**
 * Class representing an ACME DNS-01 challenge.
 *
 * @see https://tools.ietf.org/html/rfc8555#section-8.4
 */
final class ChallengeRecord
{
    private const CHALLENGE_LABEL = '_acme-challenge';

    /**
     * The domain without any subdomains (e.g. example.com).
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
     * The subdomain (optional) without the trailing dot.
     *
     * @var string|null
     */
    private $subdomain = null;

    /**
     * @param string $baseDomain the domain without any subdomains
     * @param string $validation the validation string
     */
    public function __construct(string $baseDomain, string $validation)
    {
        $this->domain = $baseDomain;
        $this->validation = $validation;
    }

    /**
     * Domain without any subdomains (should be manageable by the provider).
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * The content to be added to the DNS record (validation string).
     */
    public function getContent(): string
    {
        return $this->validation;
    }

    /**
     * Name of the DNS record to be added (e.g. _acme-challenge).
     */
    public function getName(): string
    {
        if (null === $this->subdomain) {
            return self::CHALLENGE_LABEL;
        }

        return sprintf('%s.%s', self::CHALLENGE_LABEL, $this->subdomain);
    }

    /**
     * If given subdomain contains a trailing dot, it will be stripped.
     *
     * @param string $subdomain with or without trailing dot
     */
    public function setSubdomain(string $subdomain): void
    {
        $this->subdomain = rtrim($subdomain, '.');
    }
}
