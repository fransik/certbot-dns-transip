<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\Authenticator;
use Fransik\CertbotTransip\Exception\UnableToManageDns;
use Fransik\CertbotTransip\Request;
use Fransik\CertbotTransip\Tests\Provider\TestProvider;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase
{
    private const VALIDATION = 'RzNUVnFXdGdTd0ZMY1JxalBrd1NObW5aVjZ3b1Q0YUFLeDVCVklpRVFQaQ';

    /** @var Authenticator */
    private $authenticator;

    protected function setUp(): void
    {
        parent::setUp();

        $provider = new TestProvider();
        $this->authenticator = new Authenticator($provider);
    }

    /**
     * @dataProvider provideValidDomain
     * @doesNotPerformAssertions
     */
    public function testCanHandleAuthHookForValidDomain(string $domain): void
    {
        $this->authenticator->handleAuthHook(
            new Request($domain, self::VALIDATION)
        );
    }

    /**
     * @dataProvider provideInvalidDomain
     */
    public function testCanNotHandleAuthHookForInvalidDomain(string $domain): void
    {
        $this->expectException(UnableToManageDns::class);

        $this->authenticator->handleAuthHook(
            new Request($domain, self::VALIDATION)
        );
    }

    /**
     * @dataProvider provideValidDomain
     * @doesNotPerformAssertions
     */
    public function testCanHandleCleanupHookForValidDomain(string $domain): void
    {
        $this->authenticator->handleCleanupHook(
            new Request($domain, self::VALIDATION)
        );
    }

    /**
     * @dataProvider provideInvalidDomain
     */
    public function testCanNotHandleCleanupHookForInvalidDomain(string $domain): void
    {
        $this->expectException(UnableToManageDns::class);

        $this->authenticator->handleCleanupHook(
            new Request($domain, self::VALIDATION)
        );
    }

    /**
     * @return string[][]
     */
    public function provideValidDomain(): array
    {
        return [
            ['example.com'],
            ['www.example.com'],
            ['certbot.co.uk'],
            ['test.certbot.co.uk'],
            ['dev.net'],
            ['mail.dev.net'],
            ['mail.cb.dev.net'],
        ];
    }

    /**
     * @return string[][]
     */
    public function provideInvalidDomain(): array
    {
        return [
            ['example.io'],
            ['www.example.io'],
            ['acme.co.uk'],
            ['test.acme.co.uk'],
            ['mail.cb.dev.org'],
        ];
    }
}
