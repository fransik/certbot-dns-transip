<?php

declare(strict_types=1);

namespace Fransik\CertbotTransip\Tests;

use Fransik\CertbotTransip\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function dirname;

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

    public function testCanBeCreatedFromFile(): void
    {
        $config = Config::createFromFile();

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
