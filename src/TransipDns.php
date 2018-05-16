<?php
namespace FransIk\Certbot;

class TransipDns
{
    const RECORD_NAME = '_acme-challenge';
    const TTL = 60;
    const PROPAGATION_SEC = 30;
    const PROPAGATION_TRIES = 3;
    const RESOLVERS = ['ns0.transip.net', 'ns1.transip.nl', 'ns2.transip.eu'];

    private $domain;
    private $token;
    private $baseDomain;
    private $challengeName;

    public function __construct($domain, $token)
    {
        $this->domain = $domain;
        $this->token = $token;
        $this->baseDomain = $this->findBaseDomain();
        $this->challengeName = $this->getChallengeName();
    }

    public function findBaseDomain()
    {
        $baseDomain = null;
        $domains = \Transip_DomainService::getDomainNames();
        $domainParts = explode('.', $this->domain);

        foreach ($domainParts as $part) {
            $search = implode('.', $domainParts);
            if (in_array($search, $domains, true)) {
                $baseDomain = $search;
                break;
            }
            array_shift($domainParts);
        }

        if (!$baseDomain) {
            throw new \Exception('Domain could not be found in your TransIP account');
        }

        return $baseDomain;
    }

    public function getSubdomain()
    {
        $subdomain = false;

        if ($this->domain !== $this->baseDomain) {
            $subdomain = str_replace('.' . $this->baseDomain, '', $this->domain);
        }

        return $subdomain;
    }

    public function getChallengeName()
    {
        $challengeName = self::RECORD_NAME;
        $subdomain = $this->getSubdomain();

        if($subdomain) {
            $challengeName = self::RECORD_NAME . ".${subdomain}";
        }

        return $challengeName;
    }

    private function updateChallengeRecord($create)
    {
        $challengesFound = 0;
        $dnsEntries = \Transip_DomainService::getInfo($this->baseDomain)->dnsEntries;

        foreach ($dnsEntries as $key => $dnsEntry) {
            // Challenge record already exists, overwrite it with the new challenge
            if ($dnsEntry->name === $this->challengeName) {
                if ($create) {
                    $dnsEntries[$key] = new \Transip_DnsEntry($this->challengeName, self::TTL, \Transip_DnsEntry::TYPE_TXT, $this->token);
                } else {
                    // Remove the challenge record
                    unset($dnsEntries[$key]);
                    $dnsEntries = array_values($dnsEntries);
                }
                $challengesFound++;
            }
        }

        // Create a new challenge record
        if ($create && $challengesFound === 0) {
            $dnsEntries[] = new \Transip_DnsEntry($this->challengeName, self::TTL, \Transip_DnsEntry::TYPE_TXT, $this->token);
            $challengesFound++;
        }

        if ($challengesFound > 1) {
            throw new \Exception('More than one challenge record found');
        }

        return $dnsEntries;
    }

    public function createChallengeRecord()
    {
        return $this->updateChallengeRecord(true);
    }

    public function removeChallengeRecord()
    {
        return $this->updateChallengeRecord(false);
    }

    public function commit($dnsEntries, $waitUntilPropagated = true)
    {
        \Transip_DomainService::setDnsEntries($this->baseDomain, $dnsEntries);
        if ($waitUntilPropagated && $this->isChallengePropagated()) {
            echo 'The DNS-01 challenge has been successfully deployed.' . PHP_EOL;
        }
    }

    public function isChallengePropagated()
    {
        $record = escapeshellarg($this->challengeName . '.' . $this->baseDomain);
        $expected = '"' . $this->token . '"'; // dig returns answer between double quotes

        for ($i=0; $i < self::PROPAGATION_TRIES; $i++) {
            foreach (self::RESOLVERS as $resolver) {
                $execCommand = "dig @${resolver} TXT ${record} +short";
                $output = exec($execCommand);
                if ($output === $expected) {
                    sleep(10);
                    return true;
                }
                sleep(self::PROPAGATION_SEC);
            }
        }

        return false;
    }
}
