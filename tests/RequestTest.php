<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\Request;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function putenv;

class RequestTest extends TestCase
{
    public function testCanBeCreatedFromEnvironmentVariables(): void
    {
        $domain = 'example.org';
        $validation = 'T0RjbFRraThRdXZQcWpZSGdUS3gwTG1LbEI4b3I0WEttTFJJSXFlNFo0SA';

        putenv("CERTBOT_DOMAIN=${domain}");
        putenv("CERTBOT_VALIDATION=${validation}");

        $request = Request::fromEnv();

        $this->assertSame($domain, $request->getDomain());
        $this->assertSame($validation, $request->getValidation());
    }

    public function testCanNotBeCreatedWithEmptyValues(): void
    {
        putenv('CERTBOT_DOMAIN=');

        $this->expectException(InvalidArgumentException::class);

        Request::fromEnv();
    }
}
