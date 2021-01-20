<?php

use Fransik\CertbotTransip\Config;
use Transip\Api\Library\TransipAPI;

return [
    Config::TRANSIP_LOGIN => 'transipdemo',
    Config::TRANSIP_PRIVATE_KEY => '',
    Config::TRANSIP_WHITELIST_ONLY_TOKENS => false,
    Config::TRANSIP_TOKEN => TransipAPI::TRANSIP_API_DEMO_TOKEN,
];
