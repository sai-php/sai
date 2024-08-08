<?php

declare(strict_types=1);


namespace DaggerModule;

use Dagger\Attribute\Argument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;

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
        $composerPhar = dag()
            ->container()
            ->from('composer:' . $composerVersion)
            ->file('/usr/bin/composer');

        $container = $this->baseContainer
            ->withFile('/usr/bin/composer', $composerPhar);

        return new PHPContainer($container);
    }

    #[DaggerFunction('Install a PHP extension into the container')]
    public function withExtension(
        #[Argument]
        string $extension
    ): PHPContainer {
        $extensionInstaller = dag()
            ->container()
            ->from('mlocati/php-extension-installer')
            ->file('/usr/bin/install-php-extensions');

        $container = $this->baseContainer
            ->withFile('/usr/local/bin/install-php-extensions', $extensionInstaller)
            ->withExec(
                $this->cmd('install-php-extensions ' . $extension)
            );

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