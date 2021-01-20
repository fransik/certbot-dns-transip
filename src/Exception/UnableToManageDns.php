<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Exception;

use RuntimeException;
use function sprintf;

class UnableToManageDns extends RuntimeException
{
    public static function forDomain(string $domain): self
    {
        return new self(sprintf(
            'Unable to manage DNS for domain "%s", are you sure it\'s registered & active?',
            $domain
        ));
    }
}
