<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\Argument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;

use function Dagger\dag;

#[DaggerObject]
class Php
{
    #[DaggerFunction]
    public function apache(
        #[Argument('PHP Version to use')]
        string $version = '8.3',
        #[Argument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): PHPContainer {
        $this->validateOS($os);
        return new PHPContainer(dag()->container()->from($this->makeTag(
           $version,
            'apache',
           $os,
        )));
    }

    #[DaggerFunction]
    public function cli(
        #[Argument('PHP Version to use')]
        string $version = '8.3',
        #[Argument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): PHPContainer {
        $this->validateOS($os);
        return new PHPContainer(dag()->container()->from($this->makeTag(
            $version,
            'cli',
            $os,
        )));
    }

    #[DaggerFunction]
    public function fpm(
        #[Argument('PHP Version to use')]
        string $version = '8.3',
        #[Argument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): PHPContainer {
        $this->validateOS($os);
        return new PHPContainer(dag()->container()->from($this->makeTag(
            $version,
            'fpm',
            $os,
        )));
    }

    #[DaggerFunction]
    public function zts(
        #[Argument('PHP Version to use')]
        string $version = '8.3',
        #[Argument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
    ): PHPContainer {
        $this->validateOS($os);
        return new PHPContainer(dag()->container()->from($this->makeTag(
            $version,
            'zts',
            $os,
        )));
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
}
