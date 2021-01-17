<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Provider;

use Fransik\CertbotTransip\ChallengeRecord;

interface Provider
{
    /**
     * If the provider can manage DNS for given domain, true will be returned.
     */
    public function canManageDomain(string $domainName): bool;

    /**
     * Add a DNS record for given domain that contains the ACME DNS-01 challenge.
     *
     * @see https://tools.ietf.org/html/rfc8555#section-8.4
     */
    public function createChallengeRecord(ChallengeRecord $challenge): void;

    /**
     * Remove the DNS record that contains the ACME DNS-01 challenge for given domain.
     *
     * @see https://tools.ietf.org/html/rfc8555#section-8.4
     */
    public function removeChallengeRecord(ChallengeRecord $challenge): void;
}
