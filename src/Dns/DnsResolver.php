<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Dns;

use Fransik\CertbotTransip\ChallengeRecord;

interface DnsResolver
{
    /**
     * Returns true when the challenge record can be resolved by its authoritative nameserver.
     */
    public function hasChallengeRecord(ChallengeRecord $challenge): bool;
}
