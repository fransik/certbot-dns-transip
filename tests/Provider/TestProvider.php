<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests\Provider;

use Fransik\CertbotTransip\ChallengeRecord;
use Fransik\CertbotTransip\Provider\Provider;
use function in_array;

final class TestProvider implements Provider
{
    private const AVAILABLE_DOMAINS = [
        'example.com',
        'certbot.co.uk',
        'dev.net',
        'unresolvable.local',
    ];

    /**
     * @inheritDoc
     */
    public function canManageDomain(string $domainName): bool
    {
        return in_array($domainName, self::AVAILABLE_DOMAINS, true);
    }

    /**
     * @inheritDoc
     */
    public function createChallengeRecord(ChallengeRecord $challenge): void
    {
    }

    /**
     * @inheritDoc
     */
    public function removeChallengeRecord(ChallengeRecord $challenge): void
    {
    }
}
