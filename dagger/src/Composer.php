<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;
use Dagger\Directory;
use Dagger\File;

use function Dagger\dag;

/**
 * @TODO
 * - Not much point adding update or require to this without the ability to do 2 way binding between dagger and host dirs
 * - Add autoload command so an optimised autoloaded can be created if required.
 * - Any other valuable composer commands or just allow for an interactive shell?
 */
#[DaggerObject]
final class Composer
{
    public function __construct(private Container $container)
    {
    }

    #[DaggerFunction('Perform a composer install and return the result as a directory')]
    public function install(File $composerJson, ?File $composerLock): Directory
    {
        $container = $this->container->withExec(Util::cmd('mkdir -p /tmp/dependencies/'))
            ->withWorkdir('/tmp/dependencies')
            ->withFile('/tmp/dependencies/composer.json', $composerJson)
        ;

        if ($composerLock !== null) {
            $container = $container->withFile('/tmp/dependencies/composer.lock', $composerLock);
        }

        //@TODO add parameters to this function which allow passing common options to composer.
        //Ignore platform seems useless as it should be running inside a container with all dependencies
        //what other options might be useful (no dev, optimise autoloader, ???)
        return $container->withExec(Util::cmd('composer install'))
            ->directory('/tmp/dependencies');
    }
}