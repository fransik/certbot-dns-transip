<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Exception;

use Fransik\CertbotTransip\ChallengeRecord;
use RuntimeException;
use function sprintf;

class UnableToResolve extends RuntimeException
{
    public static function challengeRecord(ChallengeRecord $challenge, int $maxTries): self
    {
        return new self(sprintf(
            'Unable to resolve DNS record "%s" after %d attempts. Try increasing the config options MAX_TRIES/WAIT_SECONDS.',
            $challenge->getFullName(),
            $maxTries
        ));
    }
}
