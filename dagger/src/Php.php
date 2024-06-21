<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerArgument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Client;
use Dagger\Container;
use Dagger\Directory;

#[DaggerObject]
class Php
{
    public Client $client;

    #[DaggerFunction]
    public function apache(
        #[DaggerArgument('PHP Version to use')]
        string $version = '8.3',
        #[DaggerArgument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): Container {
        $this->validateOS($os);
        return $this->client->container()->from($this->makeTag(
           $version,
            'apache',
           $os,
        ));
    }

    #[DaggerFunction]
    public function cli(
        #[DaggerArgument('PHP Version to use')]
        string $version = '8.3',
        #[DaggerArgument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): Container {
        $this->validateOS($os);
        return $this->client->container()->from($this->makeTag(
            $version,
            'cli',
            $os,
        ));
    }

    #[DaggerFunction]
    public function fpm(
        #[DaggerArgument('PHP Version to use')]
        string $version = '8.3',
        #[DaggerArgument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): Container {
        $this->validateOS($os);
        return $this->client->container()->from($this->makeTag(
            $version,
            'fpm',
            $os,
        ));
    }

    #[DaggerFunction]
    public function zts(
        #[DaggerArgument('PHP Version to use')]
        string $version = '8.3',
        #[DaggerArgument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): Container {
        $this->validateOS($os);
        return $this->client->container()->from($this->makeTag(
            $version,
            'zts',
            $os,
        ));
    }

    #[DaggerFunction('Install a PHP extension into a container')]
    public function withExtension(
        #[DaggerArgument('The container to install the extension into, ')]
        Container $container,
        string $extension
    ): Container {
        $extensionInstaller = $this->client
            ->container()
            ->from('mlocati/php-extension-installer')
            ->file('/usr/bin/install-php-extensions');

        $container = $container
            ->withFile('/usr/local/bin/install-php-extensions', $extensionInstaller)
            ->withExec(
                $this->cmd('install-php-extensions ' .  $extension)
            );

        return $container;
    }

    private function validateOS(string $os): void
    {
        match ($os) {
            'debian', 'alpine' => null,
            default => throw new \InvalidArgumentException('Invalid option for OS argument')
        };
    }

    private function makeTag(string $version, string $variant, string $os): string
    {
        return sprintf('php:%s-%s-%s', $version, $variant, $os);
    }

    private function cmd(string $cmd)
    {
        return ["/bin/sh", "-c", $cmd];
    }


}
