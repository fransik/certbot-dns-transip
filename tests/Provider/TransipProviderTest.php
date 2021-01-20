<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests\Provider;

use Fransik\CertbotTransip\ChallengeRecord;
use Fransik\CertbotTransip\Config;
use Fransik\CertbotTransip\Provider\TransipProvider;
use PHPUnit\Framework\TestCase;
use Transip\Api\Library\Exception\HttpBadResponseException;
use function dirname;

/**
 * @group slow
 */
class TransipProviderTest extends TestCase
{
    private const MANAGEABLE_DOMAIN = 'transipdemo.net';
    private const VALIDATION = 'ZXZhR3hmQURzNnBTUmIyTEF2OUlaZjE3RHQzanV4R0otUEN0OTJ3ci1vQQ';

    /** @var TransipProvider */
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $config = Config::createFromFile(dirname(__DIR__));
        $this->provider = new TransipProvider($config);
    }

    public function testCanManageDomain(): void
    {
        $canManageDomain = $this->provider->canManageDomain(self::MANAGEABLE_DOMAIN);

        $this->assertTrue($canManageDomain);
    }

    public function testCanNotManageUnavailableDomain(): void
    {
        $canManageDomain = $this->provider->canManageDomain('example.com');

        $this->assertFalse($canManageDomain);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCanCreateChallengeRecord(): void
    {
        $challenge = new ChallengeRecord(
            self::MANAGEABLE_DOMAIN,
            self::VALIDATION
        );

        $this->provider->createChallengeRecord($challenge);
    }

    public function testWillNotCreateChallengeRecordForUnavailableDomain(): void
    {
        $challenge = new ChallengeRecord(
            'acme.io',
            self::VALIDATION
        );

        $this->expectException(HttpBadResponseException::class);

        $this->provider->createChallengeRecord($challenge);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCanRemoveChallengeRecord(): void
    {
        $challenge = new ChallengeRecord(
            self::MANAGEABLE_DOMAIN,
            self::VALIDATION
        );

        $this->provider->removeChallengeRecord($challenge);
    }
}
