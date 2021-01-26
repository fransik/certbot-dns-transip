<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests\Dns;

use Fransik\CertbotTransip\ChallengeRecord;
use Fransik\CertbotTransip\Dns\DnsResolver;
use function in_array;

final class TestResolver implements DnsResolver
{
    private const RESOLVABLE = [
        '_acme-challenge.example.com',
        '_acme-challenge.www.example.com',
        '_acme-challenge.certbot.co.uk',
        '_acme-challenge.test.certbot.co.uk',
        '_acme-challenge.dev.net',
        '_acme-challenge.mail.dev.net',
        '_acme-challenge.mail.cb.dev.net',
    ];

    /**
     * @inheritDoc
     */
    public function hasChallengeRecord(ChallengeRecord $challenge): bool
    {
        return in_array($challenge->getFullName(), self::RESOLVABLE, true);
    }
}
