<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use Fransik\CertbotTransip\Exception\UnableToManageDns;
use Fransik\CertbotTransip\Provider\Provider;
use function substr;
use function strpos;
use function str_replace;

final class Authenticator
{
    /** @var Provider */
    private $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function handleAuthHook(Request $request): void
    {
        $challenge = $this->getChallengeRecord($request);

        $this->provider->createChallengeRecord($challenge);
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
        if (null !== $subdomain) {
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
}
