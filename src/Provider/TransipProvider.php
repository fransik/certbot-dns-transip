<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Provider;

use Fransik\CertbotTransip\ChallengeRecord;
use Fransik\CertbotTransip\Config;
use Transip\Api\Library\Entity\Domain;
use Transip\Api\Library\Entity\Domain\DnsEntry;
use Transip\Api\Library\TransipAPI;
use function array_map;
use function in_array;

final class TransipProvider implements Provider
{
    /** @var TransipAPI */
    private $api;

    /** @var string[] */
    private $domainNames = [];

    public function __construct(Config $config)
    {
        $this->api = new TransipAPI(
            $config->get(Config::TRANSIP_LOGIN, ''),
            $config->get(Config::TRANSIP_PRIVATE_KEY, ''),
            $config->get(Config::TRANSIP_WHITELIST_ONLY_TOKENS, true),
            $config->get(Config::TRANSIP_TOKEN, ''),
        );
    }

    /**
     * @inheritDoc
     */
    public function canManageDomain(string $domainName): bool
    {
        if (empty($this->domainNames)) {
            $this->domainNames = $this->getDomainNames();
        }

        return in_array($domainName, $this->domainNames, true);
    }

    /**
     * @inheritDoc
     */
    public function createChallengeRecord(ChallengeRecord $challenge): void
    {
        $repository = $this->api->domainDns();
        $dnsEntry = (new DnsEntry())
            ->setType(DnsEntry::TYPE_TXT)
            ->setExpire(60)
            ->setName($challenge->getName())
            ->setContent($challenge->getContent());

        $repository->addDnsEntryToDomain($challenge->getDomain(), $dnsEntry);
    }

    /**
     * @inheritDoc
     */
    public function removeChallengeRecord(ChallengeRecord $challenge): void
    {
        $domain = $challenge->getDomain();
        $repository = $this->api->domainDns();

        $dnsEntries = $repository->getByDomainName($domain);
        foreach ($dnsEntries as $dnsEntry) {
            if ($this->challengeEqualsDnsEntry($challenge, $dnsEntry)) {
                $repository->removeDnsEntry($domain, $dnsEntry);

                break;
            }
        }
    }

    /**
     * @return string[]
     */
    private function getDomainNames(): array
    {
        return array_map(function (Domain $domain): string {
            return $domain->getName();
        }, $this->api->domains()->getAll());
    }

    private function challengeEqualsDnsEntry(ChallengeRecord $challenge, DnsEntry $dnsEntry): bool
    {
        return $challenge->getName() === $dnsEntry->getName() &&
            $challenge->getContent() === $dnsEntry->getContent();
    }
}
