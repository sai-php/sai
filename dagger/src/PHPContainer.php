<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\Argument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;

use Dagger\Directory;
use DaggerModule\Labels\AppCodePath;
use DaggerModule\Labels\PHPContainerInfo;

use function Dagger\dag;

#[DaggerObject]
final class PHPContainer
{
    public function __construct(private Container $baseContainer)
    {
    }

    #[DaggerFunction('Install a PHP extension into the container')]
    public function withComposer(
        #[Argument]
        string $composerVersion = "2"
    ): PHPContainer {
        $containerInfo = PHPContainerInfo::fromContainer($this->baseContainer);

        if ($containerInfo->hasComposer) {
            return $this;
        }

        $containerInfo->hasComposer = true;

        $composerPhar = dag()
            ->container()
            ->from('composer:' . $composerVersion)
            ->file('/usr/bin/composer');

        $container = $this->baseContainer
            ->withFile('/usr/bin/composer', $composerPhar)
            ->withLabel(PHPContainerInfo::LABEL, (string) $containerInfo);

        return new PHPContainer($container);
    }

    #[DaggerFunction('Install a PHP extension into the container')]
    public function withExtension(
        #[Argument]
        string $extension
    ): PHPContainer {
        $containerInfo = PHPContainerInfo::fromContainer($this->baseContainer);

        if (in_array($extension, $containerInfo->extensions)) {
            return $this;
        }

        $container = $this->baseContainer;

        if (!$containerInfo->hasExtensionInstaller) {

            $extensionInstaller = dag()
                ->container()
                ->from('mlocati/php-extension-installer')
                ->file('/usr/bin/install-php-extensions');

            $container = $container->withFile('/usr/local/bin/install-php-extensions', $extensionInstaller);
            $containerInfo->hasExtensionInstaller = true;
        }

        $containerInfo->extensions[] = $extension;

        $container = $container
            ->withExec(
                $this->cmd('install-php-extensions ' . $extension)
            )
            ->withLabel(PHPContainerInfo::LABEL, (string) $containerInfo);

        return new PHPContainer($container);
    }

    #[DaggerFunction('Add application code to container')]
    public function withAppCode(Directory $appCode, string $path = '/app'): PHPContainer
    {
        $container = $this->baseContainer->withExec($this->cmd('mkdir -p ' . $path))
            ->withDirectory($path, $appCode)
            ->withWorkdir($path)
            ->withLabel(AppCodePath::LABEL, (string) (new AppCodePath($path)));

        return new PHPContainer($container);
    }

    #[DaggerFunction('Returns the built PHP container')]
    public function container(): Container
    {
        return $this->baseContainer;
    }

    private function cmd(string $cmd)
    {
        return ["/bin/sh", "-c", $cmd];
    }
}