<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function dirname;
use function putenv;
use function file_exists;
use function copy;
use function unlink;

class ConfigTest extends TestCase
{
    public function testCanGetPassedOptions(): void
    {
        $config = new Config(['option' => 'value']);

        $this->assertSame('value', $config->get('option'));
    }

    public function testReturnsDefaultValue(): void
    {
        $config = new Config([]);

        $this->assertNull($config->get('option'));
        $this->assertSame('default', $config->get('option', 'default'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReturnsEnvValueWhenSet(): void
    {
        $config = new Config(['option' => 'non-env-value']);

        putenv('OPTION=env-value');

        $this->assertSame('env-value', $config->get('option'));
    }

    public function testCanBeCreatedFromFile(): void
    {
        $baseDir = dirname(__DIR__);
        $defaultConfig = $baseDir.'/config.php';
        $distConfig = $baseDir.'/config.php.dist';

        if ($configDoesNotExist = !file_exists($defaultConfig)) {
            copy($distConfig, $defaultConfig);
        }

        $config = Config::createFromFile();

        if ($configDoesNotExist) {
            unlink($defaultConfig);
        }

        $this->assertSame('', $config->get(Config::TRANSIP_LOGIN));
    }

    public function testCanBeCreatedFromFileInDirectory(): void
    {
        $config = Config::createFromFile(__DIR__);

        $this->assertSame('transipdemo', $config->get(Config::TRANSIP_LOGIN));
    }

    public function testCanNotBeCreatedWithNonExistentFile(): void
    {
        $this->expectException(RuntimeException::class);

        Config::createFromFile(dirname(__DIR__).'/bin');
    }
}
