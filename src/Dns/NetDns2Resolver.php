<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Dns;

use Fransik\CertbotTransip\ChallengeRecord;
use Net_DNS2_Exception;
use Net_DNS2_Resolver;
use Net_DNS2_RR_NS;
use Net_DNS2_RR_A;
use function count;
use function array_merge;
use function array_map;

final class NetDns2Resolver implements DnsResolver
{
    /** @var Net_DNS2_Resolver */
    private $resolver;

    public function __construct()
    {
        $this->resolver = new Net_DNS2_Resolver();
    }

    /**
     * @inheritDoc
     */
    public function hasChallengeRecord(ChallengeRecord $challenge): bool
    {
        $this->useAuthoritativeNameservers($challenge->getDomain());

        try {
            $query = $this->resolver->query($challenge->getFullName(), 'TXT');

            return count($query->answer) > 0;
        } catch (Net_DNS2_Exception $e) {
            return false;
        }
    }

    private function useAuthoritativeNameservers(string $domain): void
    {
        $nameservers = $this->getNameservers($domain);
        if (count($nameservers) > 0) {
            $this->resolver->setServers($nameservers);
        }
    }

    /**
     * @return string[]
     */
    private function getNameservers(string $domain): array
    {
        try {
            $query = $this->resolver->query($domain, 'NS');

            return array_merge(...array_map(
                [$this, 'getNameserverIps'],
                $query->answer
            ));
        } catch (Net_DNS2_Exception $e) {
            return [];
        }
    }

    /**
     * @return string[]
     */
    private function getNameserverIps(Net_DNS2_RR_NS $nameserver): array
    {
        try {
            $query = $this->resolver->query($nameserver->nsdname);

            return array_map(function (Net_DNS2_RR_A $ip): string {
                return $ip->address;
            }, $query->answer);
        } catch (Net_DNS2_Exception $e) {
            return [];
        }
    }
}
