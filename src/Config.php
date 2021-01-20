<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip;

use RuntimeException;
use function dirname;
use function file_exists;
use function sprintf;

final class Config
{
    public const TRANSIP_LOGIN = 'transip_login';
    public const TRANSIP_PRIVATE_KEY = 'transip_private_key';
    public const TRANSIP_WHITELIST_ONLY_TOKENS = 'transip_whitelist_only_tokens';
    public const TRANSIP_TOKEN = 'transip_token';

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
        return $this->options[$option] ?? $default;
    }
}
