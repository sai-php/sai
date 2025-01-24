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
            ->withExec(Util::cmd('install-php-extensions ' . $extension))
            ->withLabel(PHPContainerInfo::LABEL, (string) $containerInfo);

        return new PHPContainer($container);
    }

    #[DaggerFunction('Install composer into the container, useful if you need a container with composer in it, but to run composer commands see the composer() function.')]
    public function withComposer(
        #[Argument]
        string $composerVersion = "2",
        #[Argument('Dagger cache name to mount for the composer cache to speed up builds')]
        string $composerCacheName="sai-composer-cache"
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
            ->withLabel(PHPContainerInfo::LABEL, (string) $containerInfo)
            ->withMountedCache('/tmp/.composer', dag()->cacheVolume($composerCacheName))
            ->withEnvVariable('COMPOSER_HOME', '/tmp/.composer');

        //@TODO, need to do some work to manage & label the user accounts used within the container and ensure file permissions are correct
        //->withEnvVariable('COMPOSER_ALLOW_SUPERUSER', '1');

        return new PHPContainer($container);
    }

    #[DaggerFunction('Returns a composer object which can operate with your container')]
    public function composer(
        #[Argument('Composer version to use, suggested values are 1 or 2 however any valid composer docker tag name is accepted.')]
        string $composerVersion = "2",
        #[Argument('Dagger cache name to mount for the composer cache to speed up builds.')]
        string $composerCacheName="sai-composer-cache"
    ): Composer {
        $container = $this->withComposer($composerVersion, $composerCacheName)
            ->container();

        return new Composer($container);
    }

    #[DaggerFunction('Add application code to container')]
    public function withAppCode(Directory $appCode, string $path = '/app'): PHPContainer
    {
        $container = $this->baseContainer->withExec(Util::cmd('mkdir -p ' . $path))
            ->withExec(Util::cmd('chown www-data:www-data ' . $path))
            ->withDirectory($path, $appCode, owner: "www-data:www-data")
            ->withWorkdir($path)
            ->withLabel(AppCodePath::LABEL, (string) (new AppCodePath($path)));

        return new PHPContainer($container);
    }

    #[DaggerFunction('Adds application code which uses composer to manage dependencies. Requires a composer.json in the root of the directory. Will do an install of the dependencies using Composer.')]
    public function withComposerApp(
        Directory $appCode,
        string $path = '/app',
        string $composerVersion = '2',
        string $composerCacheName = 'sai-composer-cache',
    ): PHPContainer {
        $composerJson = $appCode->file('composer.json');
        $directoryListing = $appCode->entries();

        if (in_array('composer.lock', $directoryListing)) {
            $composerLock = $appCode->file('composer.lock');
        }

        $dependencies = $this->composer($composerVersion, $composerCacheName)
            ->install($composerJson, $composerLock ?? null);

        return $this->withAppCode($appCode, $path)
            ->withAppCode($dependencies, $path);
    }

    #[DaggerFunction('Returns the built PHP container')]
    public function container(): Container
    {
        return $this->baseContainer;
    }
}