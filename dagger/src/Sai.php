<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\Argument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;

use Dagger\Container;

use DaggerModule\Labels\NginxContainerInfo;
use DaggerModule\Labels\PHPContainerInfo;

use function Dagger\dag;

#[DaggerObject]
final class Sai
{
    #[DaggerFunction]
    public function php(
        #[Argument('PHP Version to use')]
        string $version = '8.3',
        #[Argument('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
        #[Argument('Image variant, valid options: apache, cli, fpm, zts')]
        string $variant = 'cli',
    ): PHPContainer {

        $containerInfo = new PHPContainerInfo(
            $version,
            $os,
            $variant
        );

        $baseContainer = dag()
            ->container()
            ->from($containerInfo->getTag())
            ->withLabel(PHPContainerInfo::LABEL, (string) $containerInfo);

        return new PHPContainer($baseContainer);
    }

    #[DaggerFunction]
    public function adoptPHPContainer(Container $container): PHPContainer
    {
        //validates that this container was originally created by Sai
        PHPContainerInfo::fromContainer($container);

        return new PHPContainer($container);
    }

    #[DaggerFunction]
    public function nginx(
        #[Argument('Nginx Version to use')]
        string $version = '1.27',
        #[Argument('OS Version to use, valid options: alpine, debian, bookworm')]
        string $os = 'alpine',
        #[Argument('Image variant, valid options: slim, perl, otel')]
        ?string $variant = null,
    ): NginxContainer
    {
        $containerInfo = new NginxContainerInfo(
            $version,
            $os,
            $variant
        );

        $baseContainer = dag()
            ->container()
            ->from($containerInfo->getTag())
            ->withLabel(NginxContainerInfo::LABEL, (string) $containerInfo);

        return new NginxContainer($baseContainer);
    }
}