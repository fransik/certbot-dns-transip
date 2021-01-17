<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\ChallengeRecord;
use PHPUnit\Framework\TestCase;

class ChallengeRecordTest extends TestCase
{
    public function testReturnsExpectedValues(): void
    {
        $baseDomain = 'example.com';
        $validation = 'za1iqw739123xxZ#qqq2';

        $challenge = new ChallengeRecord($baseDomain, $validation);

        $this->assertSame($baseDomain, $challenge->getDomain());
        $this->assertSame($validation, $challenge->getContent());
        $this->assertSame('_acme-challenge', $challenge->getName());
    }

    /**
     * @dataProvider provideSubdomains
     */
    public function testReturnsExpectedValuesWhenSubdomainIsSet(string $subdomain, string $expectedName): void
    {
        $baseDomain = 'acme.co.uk';
        $validation = 'xr94dulPoXaiHC3QeRzK0ObmQvRM9Z0U';

        $challenge = new ChallengeRecord($baseDomain, $validation);
        $challenge->setSubdomain($subdomain);

        $this->assertSame($baseDomain, $challenge->getDomain());
        $this->assertSame($validation, $challenge->getContent());
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
