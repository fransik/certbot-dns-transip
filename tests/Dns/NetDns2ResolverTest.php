<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests\Dns;

use Fransik\CertbotTransip\ChallengeRecord;
use Fransik\CertbotTransip\Dns\NetDns2Resolver;
use PHPUnit\Framework\TestCase;

/**
 * @group slow
 */
class NetDns2ResolverTest extends TestCase
{
    /** @var NetDns2Resolver */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new NetDns2Resolver();
    }


    public function testNonExistentChallengeShouldNotResolve(): void
    {
        $canResolve = $this->resolver->hasChallengeRecord(
            new ChallengeRecord('transipdemo.net', 'abc')
        );

        $this->assertFalse($canResolve);
    }
}
