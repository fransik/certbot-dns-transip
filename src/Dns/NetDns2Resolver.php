<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Dns;

use Fransik\CertbotTransip\ChallengeRecord;
use Net_DNS2_Exception;
use Net_DNS2_Resolver;
use Net_DNS2_RR_NS;
use Net_DNS2_RR_A;
use Net_DNS2_RR_TXT;
use function count;
use function array_filter;
use function array_merge;
use function array_map;
use function in_array;

final class NetDns2Resolver implements DnsResolver
{
    /** @var Net_DNS2_Resolver */
    private $resolver;

    public function __construct()
    {
        $this->resolver = new Net_DNS2_Resolver([
            'ns_random' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function hasChallengeRecord(ChallengeRecord $challenge): bool
    {
        $validRecords = 0;
        $nameservers = $this->getNameservers($challenge->getDomain());

        foreach ($nameservers as $nameserver) {
            $this->resolver->setServers([$nameserver]);

            $records = $this->getTxtRecords($challenge->getFullName());

            if ($this->challengeIsValid($records, $challenge->getContent())) {
                $validRecords++;
            }
        }

        return $validRecords === count($nameservers);
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

    /**
     * @return Net_DNS2_RR_TXT[]
     */
    private function getTxtRecords(string $name): array
    {
        try {
            $query = $this->resolver->query($name, 'TXT');

            return $query->answer;
        } catch (Net_DNS2_Exception $e) {
            return [];
        }
    }

    /**
     * @param Net_DNS2_RR_TXT[] $records
     */
    private function challengeIsValid(array $records, string $challengeContent): bool
    {
        $validRecords = array_filter($records, function ($record) use ($challengeContent) {
            return in_array($challengeContent, $record->text, true);
        });

        return count($validRecords) > 0;
    }
}
