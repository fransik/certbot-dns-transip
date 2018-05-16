# certbot-dns-transip
This package automates the process of completing a DNS-01 challenge for domains using the [TransIP DNS service](https://www.transip.eu/domain-name/).
The auth script is invoked by [Certbot](https://certbot.eff.org/)'s `--manual-auth-hook`, which then creates the required challenge record using the TransIP API. After validation the `--manual-cleanup-hook` is invoked and the challenge record is removed again.

## Setup
### Requirements
* Certbot
* PHP & Composer
* dig - DNS lookup utility
* A TransIP account (and the domain registered you want a certificate for ;-)
* API access enabled for this account (https://www.transip.eu/knowledgebase/entry/77-want-use-the-transip-api/)
* The private key generated in above process
* When using API whitelist, make sure the public IP of the machine that is running this script is whitelisted

### Installation
* Clone this repository
* Run `composer install`
* Update `vendor/transip/api/Transip/ApiSettings.php` with your TransIP login name and private key
* Symlink `bin/transip-dns-auth` and `bin/transip-dns-cleanup` to something in your `$PATH`

## Usage
Ok, the boring part is over.. let's get a shiny Letsencrypt certificate :)

Example:

`# certbot certonly --manual --preferred-challenges=dns --manual-auth-hook transip-dns-auth --manual-cleanup-hook transip-dns-cleanup -d example.com -d www.example.com`

## Limitations
Currently tested with a limited number of domains. Please be careful as this script overwrites your DNS zone (keeping all the current records, of course).

You can always do a test run by commenting out `$transipDns->commit()` and printing the `$dnsEntries` array in the `transip-dns-auth` and `transip-dns-cleanup` scripts to make sure nothing weird happens to your zone.
