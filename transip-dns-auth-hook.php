#!/usr/bin/php
<?php
require_once('Transip/DomainService.php');

$domain = getenv('CERTBOT_DOMAIN');
$token = getenv('CERTBOT_VALIDATION');

echo 'Deploying DNS-01 challenge for [' . $domain . ']...' . PHP_EOL;

$baseDomain = findBaseDomain($domain);
$dnsEntries = createChallengeRecord($domain, $baseDomain, $token);

echo 'DNS records to be updated:' . PHP_EOL;
print_r($dnsEntries);
saveDnsEntries($baseDomain, $dnsEntries);

function getDomainNames()
{
	$domains = array();

	try
	{
		$domains = Transip_DomainService::getDomainNames();
	}
	catch(SoapFault $e)
	{
		echo '[ERROR] ' . $e->getMessage(), PHP_EOL;
		exit(1);
	}

	return $domains;
}

function findBaseDomain($domain)
{
	$baseDomain = NULL;
	$domains = getDomainNames();
	$domainParts = explode('.', $domain);

	foreach($domainParts as $part)
	{
		$search = implode('.', $domainParts);
		if(in_array($search, $domains, TRUE))
		{
			$baseDomain = $search;
			break;
		}
		array_shift($domainParts);
	}

	if(!$baseDomain)
	{
		echo '[ERROR] Domain could not be found in TransIP account.' . PHP_EOL;
		exit(1);
	}

	return $baseDomain;
}

function getSubdomain($domain, $baseDomain)
{
	$subdomain = FALSE;

	if($domain !== $baseDomain)
	{
		$toReplace = '.' . $baseDomain;
		$subdomain = str_replace($toReplace, '', $domain);
	}

	return $subdomain;
}

function getDnsEntries($baseDomain)
{
	$dnsEntries = array();

	try
	{
		$dnsEntries = Transip_DomainService::getInfo($baseDomain)->dnsEntries;
	}
	catch(SoapFault $e)
	{
		echo '[ERROR] ' . $e->getMessage(), PHP_EOL;
		exit(1);
	}

	return $dnsEntries;
}

function getChallengeName($domain, $baseDomain)
{
	$recordName = '_acme-challenge';
	$challengeName = $recordName;
	$subdomain = getSubdomain($domain, $baseDomain);

	if($subdomain)
	{
		$challengeName = $recordName . '.' . $subdomain;
	}

	return $challengeName;
}

function createChallengeRecord($domain, $baseDomain, $token)
{
	$ttl = 60;
	$challengesFound = 0;
	$dnsEntries = getDnsEntries($baseDomain);
	$challengeName = getChallengeName($domain, $baseDomain);

	foreach($dnsEntries as $key => $dnsEntry)
	{
		// Challenge record already exists, overwrite it with the new challenge
		if($dnsEntry->name === $challengeName)
		{
			$dnsEntries[$key] = new Transip_DnsEntry($challengeName, $ttl, Transip_DnsEntry::TYPE_TXT, $token);
			$challengesFound++;
		}
	}

	// Create a new challenge record
	if($challengesFound === 0)
	{
		$dnsEntries[] = new Transip_DnsEntry($challengeName, $ttl, Transip_DnsEntry::TYPE_TXT, $token);
		$challengesFound++;
	}

	if($challengesFound > 1)
	{
		echo '[ERROR] More than one challenge record found.' . PHP_EOL;
		exit(1);
	}

	return $dnsEntries;
}

function saveDnsEntries($baseDomain, $dnsEntries)
{
	try
	{
		// Commit the changes to TransIP's DNS service
		Transip_DomainService::setDnsEntries($baseDomain, $dnsEntries);
		echo 'The DNS-01 challenge has been successfully deployed.';
	}
	catch(SoapFault $e)
	{
		echo '[ERROR] ' . $e->getMessage(), PHP_EOL;
		exit(1);
	}
}
?>
