<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\ChallengeRecord;
use PHPUnit\Framework\TestCase;

class ChallengeRecordTest extends TestCase
{
    private const CHALLENGE = '_acme-challenge';
    private const VALIDATION = 'NXk0cjk0M1ZmeE5JRW1lNkgyeFp0cVpkaktFcWo1RktGeGlwd292UGt3Yw';

    public function testReturnsExpectedValues(): void
    {
        $baseDomain = 'example.com';

        $challenge = new ChallengeRecord($baseDomain, self::VALIDATION);

        $this->assertSame($baseDomain, $challenge->getDomain());
        $this->assertSame(self::VALIDATION, $challenge->getContent());
        $this->assertSame(self::CHALLENGE, $challenge->getName());
        $this->assertSame(
            self::CHALLENGE.'.'.$baseDomain,
            $challenge->getFullName()
        );
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
        $this->assertSame(
            $expectedName.'.'.$baseDomain,
            $challenge->getFullName()
        );
    }

    /**
     * @return string[][]
     */
    public function provideSubdomains(): array
    {
        return [
            ['www', self::CHALLENGE.'.www'],
            ['www.', self::CHALLENGE.'.www'],
            ['mail.cs', self::CHALLENGE.'.mail.cs'],
            ['mail.cs.', self::CHALLENGE.'.mail.cs'],
        ];
    }
}
