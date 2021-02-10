<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use RuntimeException;
use function dirname;
use function file_exists;
use function getenv;
use function sprintf;
use function strtoupper;

final class Config
{
    public const TRANSIP_LOGIN = 'transip_login';
    public const TRANSIP_PRIVATE_KEY = 'transip_private_key';
    public const TRANSIP_WHITELIST_ONLY_TOKENS = 'transip_whitelist_only_tokens';
    public const TRANSIP_TOKEN = 'transip_token';
    public const WAIT_SECONDS = 'wait_seconds';
    public const MAX_TRIES = 'max_tries';

    private const CONFIG_FILENAME = 'config.php';

    /** @var mixed[] */
    private $options;

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public static function createFromFile(string $configDirectory = null): self
    {
        $configDirectory = $configDirectory ?? dirname(__DIR__);
        $configFilePath = "${configDirectory}/".self::CONFIG_FILENAME;

        if (!file_exists($configFilePath)) {
            throw new RuntimeException(sprintf('Config file "%s" does not exist', $configFilePath));
        }

        $config = include $configFilePath ?? [];

        return new self($config);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $option, $default = null)
    {
        $envOption = getenv(strtoupper($option));
        if ($envOption !== false) {
            return $envOption;
        }

        return $this->options[$option] ?? $default;
    }
}
