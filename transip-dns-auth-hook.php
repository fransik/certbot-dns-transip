#!/usr/bin/php
<?php
require_once('Transip/DomainService.php');

const TTL = 60;
const CHALLENGE_NAME = '_acme-challenge';

$domain = getenv('CERTBOT_DOMAIN');
$token = getenv('CERTBOT_VALIDATION');
$baseDomain = findBaseDomain($domain);
$subdomain = getSubdomain($domain, $baseDomain);
$dnsEntries = getDnsEntries($baseDomain);

echo 'Deploying DNS-01 challenge for ' . $domain . '...' . PHP_EOL;
echo 'Base: ' . $baseDomain . ', Sub: ' . $subdomain, PHP_EOL;

deployChallenge($baseDomain, $subdomain, $dnsEntries, $token);

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
	return str_replace('.' . $baseDomain, '', $domain);
}

function getDnsEntries($domain)
{
	$dnsEntries = array();

	try
	{
		$dnsEntries = Transip_DomainService::getInfo($domain)->dnsEntries;
	}
	catch(SoapFault $e)
	{
		echo '[ERROR] ' . $e->getMessage(), PHP_EOL;
		exit(1);
	}

	return $dnsEntries;
}

function deployChallenge($domain, $subdomain, $dnsEntries, $token)
{
	$challengesFound = 0;
	$challengeName = CHALLENGE_NAME;

	if($subdomain)
	{
		$challengeName = CHALLENGE_NAME . '.' . $subdomain;
	}

	foreach($dnsEntries as $key => $dnsEntry)
	{
		// Challenge record already exists, overwrite it with the new challenge
		if($dnsEntry->name === $challengeName)
		{
			$dnsEntries[$key] = new Transip_DnsEntry($challengeName, TTL, Transip_DnsEntry::TYPE_TXT, $token);
			$challengesFound++;
		}
	}

	// Create a new challenge record
	if($challengesFound === 0)
	{
		$dnsEntries[] = new Transip_DnsEntry($challengeName, TTL, Transip_DnsEntry::TYPE_TXT, $token);
		$challengesFound++;
	}

	if($challengesFound > 1)
	{
		echo '[ERROR] More than one challenge record found.' . PHP_EOL;
		exit(1);
	}

	print_r($dnsEntries);

	try
	{
		// Commit the changes to TransIP's DNS service
		Transip_DomainService::setDnsEntries($domain, $dnsEntries);
		echo 'The DNS-01 challenge has been successfully deployed.';
	}
	catch(SoapFault $e)
	{
		echo '[ERROR] ' . $e->getMessage(), PHP_EOL;
		exit(1);
	}
}
?>
