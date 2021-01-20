<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\ChallengeRecord;
use PHPUnit\Framework\TestCase;

class ChallengeRecordTest extends TestCase
{
    private const VALIDATION = 'NXk0cjk0M1ZmeE5JRW1lNkgyeFp0cVpkaktFcWo1RktGeGlwd292UGt3Yw';

    public function testReturnsExpectedValues(): void
    {
        $baseDomain = 'example.com';

        $challenge = new ChallengeRecord($baseDomain, self::VALIDATION);

        $this->assertSame($baseDomain, $challenge->getDomain());
        $this->assertSame(self::VALIDATION, $challenge->getContent());
        $this->assertSame('_acme-challenge', $challenge->getName());
    }

    /**
     * @dataProvider provideSubdomains
     */
    public function testReturnsExpectedValuesWhenSubdomainIsSet(string $subdomain, string $expectedName): void
    {
        $baseDomain = 'acme.co.uk';

        $challenge = new ChallengeRecord($baseDomain, self::VALIDATION);
        $challenge->setSubdomain($subdomain);

        $this->assertSame($baseDomain, $challenge->getDomain());
        $this->assertSame(self::VALIDATION, $challenge->getContent());
        $this->assertSame($expectedName, $challenge->getName());
    }

    /**
     * @return string[][]
     */
    public function provideSubdomains(): array
    {
        return [
            ['www', '_acme-challenge.www'],
            ['www.', '_acme-challenge.www'],
            ['mail.cs', '_acme-challenge.mail.cs'],
            ['mail.cs.', '_acme-challenge.mail.cs'],
        ];
    }
}
