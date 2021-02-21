# Certbot DNS TransIP :closed_lock_with_key:

[![Release](https://img.shields.io/github/v/release/fransik/certbot-dns-transip?label=stable)](https://github.com/fransik/certbot-dns-transip/releases)
[![License](https://img.shields.io/github/license/fransik/certbot-dns-transip)](LICENSE)
[![CI](https://github.com/fransik/certbot-dns-transip/actions/workflows/ci.yml/badge.svg)](https://github.com/fransik/certbot-dns-transip/actions/workflows/ci.yml)
[![Docker Image](https://github.com/fransik/certbot-dns-transip/actions/workflows/docker-image.yml/badge.svg)](https://github.com/fransik/certbot-dns-transip/actions/workflows/docker-image.yml)
[![codecov](https://codecov.io/gh/fransik/certbot-dns-transip/branch/main/graph/badge.svg?token=XBI8HH6HKJ)](https://codecov.io/gh/fransik/certbot-dns-transip)

Looking for a way to get a [Let's Encrypt](https://letsencrypt.org/) (wildcard) certificate for the domain(s) that you registered with [TransIP](https://www.transip.eu/)?

This script automates the process of completing a DNS-01 challenge for domains using the TransIP DNS service.
The auth script is invoked by [Certbot's](https://certbot.eff.org/) `--manual-auth-hook`, which then creates the required challenge record using the TransIP API. After validation the `--manual-cleanup-hook` is invoked and the challenge record is removed again.

## Requirements
* A TransIP account with [API access enabled](https://www.transip.eu/knowledgebase/entry/77-using-the-transip-rest-api/#enabling_the_api_access_and_whitelisting)
* Docker *or*:
    * Certbot
    * Composer
    * PHP >= 7.3 with JSON and OpenSSL extensions enabled

## Usage
This describes how to use the script via Docker. If you want to use the script on a system with Certbot & PHP installed [follow these instructions](#manual-installation).

* Create a `.env` file with the following content:
```dotenv
TRANSIP_LOGIN=YOUR-LOGIN
TRANSIP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----XXXXX-----END PRIVATE KEY-----"
```
* Make sure your entire [private key](https://www.transip.eu/knowledgebase/entry/77-using-the-transip-rest-api/#generating_a_key_pair) is on a single line (removing all newlines/spaces) and between double quotes
* Run:
```shell
docker run -it --rm \
    --env-file /path/to/.env \
    -v "/etc/letsencrypt:/etc/letsencrypt" \
    -v "/var/lib/letsencrypt:/var/lib/letsencrypt" \
    fransik/certbot-dns-transip
```

After validation succeeds (this can take up to 10 minutes) you can find the certificate here: `/etc/letsencrypt/live`.

### Certificate renewal
Make sure the following command runs daily (via cron for example):
```shell
docker run --rm \
    --env-file /path/to/.env \
    -v "/etc/letsencrypt:/etc/letsencrypt" \
    -v "/var/lib/letsencrypt:/var/lib/letsencrypt" \
    fransik/certbot-dns-transip renew
```

### Test certificate
To request a test certificate run:
```shell
docker run -it --rm \
    --env-file /path/to/.env \
    -v "/etc/letsencrypt:/etc/letsencrypt" \
    -v "/var/lib/letsencrypt:/var/lib/letsencrypt" \
    fransik/certbot-dns-transip certonly \
    --test-cert \
    --preferred-challenges=dns \
    --manual \
    --manual-auth-hook bin/auth \
    --manual-cleanup-hook bin/cleanup
```

## Manual installation
* Clone this repository
* Run `composer install --no-dev`
* Rename `config.php.dist` to `config.php`
* Update `config.php` with your TransIP login name and [private key](https://www.transip.eu/knowledgebase/entry/77-using-the-transip-rest-api/#generating_a_key_pair)
* Run:
```shell
certbot certonly \
    --preferred-challenges=dns \
    --manual \
    --manual-auth-hook /path/to/certbot-dns-transip/bin/auth \
    --manual-cleanup-hook /path/to/certbot-dns-transip/bin/cleanup \
    -d example.com -d "*.example.com"
```

After validation succeeds (this can take up to 10 minutes) you can find the certificate here: `/etc/letsencrypt/live`.

### Certificate renewal
Should be automatic on most systems that have the certbot package installed. See [certbot docs](https://certbot.eff.org/docs/using.html#renewing-certificates).

### Test certificate
To request a test certificate run:
```shell
certbot certonly \
    --test-cert \
    --preferred-challenges=dns \
    --manual \
    --manual-auth-hook /path/to/certbot-dns-transip/bin/auth \
    --manual-cleanup-hook /path/to/certbot-dns-transip/bin/cleanup \
    -d example.com -d "*.example.com"
```
