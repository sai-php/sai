<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\Doc;
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
        #[Doc('PHP Version to use')]
        string $version = '8.5',
        #[Doc('OS Version to use, valid options: alpine, debian')]
        string $os = 'alpine',
        #[Doc('Image variant, valid options: apache, cli, fpm, zts')]
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
        #[Doc('Nginx Version to use')]
        string $version = '1.31',
        #[Doc('OS Version to use, valid options: alpine, trixie')]
        string $os = 'alpine',
        #[Doc('Image variant, valid options: slim, perl, otel')]
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

    #[DaggerFunction]
    public function qaTools(?Container $container = null): QaTools
    {
        if ($container === null) {
            $phpContainer = $this->php();
        } else {
            $phpContainer = $this->adoptPHPContainer($container);
        }

        return new QaTools($phpContainer);
    }
}