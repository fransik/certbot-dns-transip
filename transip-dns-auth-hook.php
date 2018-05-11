#!/usr/bin/php
<?php
require_once('Transip/DomainService.php');

$domain = getenv('CERTBOT_DOMAIN');
$validation = getenv('CERTBOT_VALIDATION');
$baseDomain = findBaseDomain($domain);
$dnsEntries = getDnsEntries($baseDomain);

echo 'DOMAIN: ' . $domain, PHP_EOL;
echo 'VALIDATION: ' . $validation, PHP_EOL;
echo 'BASE DOMAIN: ' . $baseDomain, PHP_EOL;
print_r($dnsEntries);

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

	foreach ($domainParts as $part)
	{
		$search = implode('.', $domainParts);
		if (in_array($search, $domains, TRUE))
		{
			$baseDomain = $search;
			break;
		}
		array_shift($domainParts);
	}

	if (! $baseDomain)
	{
		echo '[ERROR] Domain could not be found in TransIP account.' . PHP_EOL;
		exit(1);
	}

	return $baseDomain;
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
