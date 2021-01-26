<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use Fransik\CertbotTransip\Dns\DnsResolver;
use Fransik\CertbotTransip\Exception\UnableToManageDns;
use Fransik\CertbotTransip\Exception\UnableToResolve;
use Fransik\CertbotTransip\Provider\Provider;
use function substr;
use function strpos;
use function str_replace;
use function sleep;

final class Authenticator
{
    /** @var Provider */
    private $provider;

    /** @var DnsResolver */
    private $resolver;

    /**
     * Amount of seconds to wait between attempts to resolve the challenge record.
     *
     * @var int
     */
    private $waitSeconds;

    /**
     * Maximum amount of times resolving the challenge record should be attempted.
     *
     * @var int
     */
    private $maxTries;

    public function __construct(Provider $provider, DnsResolver $resolver, Config $config)
    {
        $this->provider = $provider;
        $this->resolver = $resolver;
        $this->waitSeconds = (int) $config->get(Config::WAIT_SECONDS, 30);
        $this->maxTries = (int) $config->get(Config::MAX_TRIES, 10);
    }

    public function handleAuthHook(Request $request): void
    {
        $challenge = $this->getChallengeRecord($request);

        $this->provider->createChallengeRecord($challenge);
        $this->waitUntilChallengeRecordResolves($challenge);
    }

    public function handleCleanupHook(Request $request): void
    {
        $challenge = $this->getChallengeRecord($request);

        $this->provider->removeChallengeRecord($challenge);
    }

    private function getChallengeRecord(Request $request): ChallengeRecord
    {
        $domain = $request->getDomain();
        $baseDomain = $this->getBaseDomain($domain);
        $subdomain = $this->findSubdomain($domain, $baseDomain);

        $challenge = new ChallengeRecord($baseDomain, $request->getValidation());
        if ($subdomain !== null) {
            $challenge->setSubdomain($subdomain);
        }

        return $challenge;
    }

    private function getBaseDomain(string $domain): string
    {
        $initialDomain = $domain;

        while (strpos($domain, '.') !== false) {
            if ($this->provider->canManageDomain($domain)) {
                return $domain;
            }

            $domain = (string) substr($domain, strpos($domain, '.') + 1);
        }

        throw UnableToManageDns::forDomain($initialDomain);
    }

    private function findSubdomain(string $domain, string $baseDomain): ?string
    {
        if ($domain === $baseDomain) {
            return null; // Domain doesn't contain a subdomain
        }

        return str_replace($baseDomain, '', $domain);
    }

    private function waitUntilChallengeRecordResolves(ChallengeRecord $challenge): void
    {
        for ($tries = 1; $tries <= $this->maxTries; $tries++) {
            if ($this->resolver->hasChallengeRecord($challenge)) {
                break;
            }

            if ($tries === $this->maxTries) {
                throw UnableToResolve::challengeRecord($challenge, $this->maxTries);
            }

            sleep($this->waitSeconds);
        }

        sleep($this->waitSeconds); // Wait another round just to be sure DNS is fully propagated
    }
}
