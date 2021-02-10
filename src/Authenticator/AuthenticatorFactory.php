<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Authenticator;

use Fransik\CertbotTransip\Config;
use Fransik\CertbotTransip\Dns\NetDns2Resolver;
use Fransik\CertbotTransip\Provider\TransipProvider;

final class AuthenticatorFactory
{
    public static function create(): Authenticator
    {
        $config = Config::createFromFile();

        return new Authenticator(
            new TransipProvider($config),
            new NetDns2Resolver(),
            $config
        );
    }
}
