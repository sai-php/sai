<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;

use Dagger\ReturnType;
use DaggerModule\Labels\AppCodePath;
use DaggerModule\Labels\PHPContainerInfo;

use DaggerModule\Reports\PHPStanReport;

use function Dagger\dag;

#[DaggerObject]
final class QaTools
{
    public function __construct(private PHPContainer $container)
    {
    }

    #[DaggerFunction]
    #[Doc('Runs PHPStan against your codebase')]
    //@TODO allow passing args to php stan eg report format
    public function phpStan(): PHPStanReport
    {
        $container = $this->container->container();
        $appCodePath = AppCodePath::fromContainer($container);
        $existingWorkDir = $container->workdir();

        if ($existingWorkDir !== $appCodePath->appCodePath) {
            $container = $container->withWorkDir($appCodePath->appCodePath);
        }

        //@TODO check that a composer install has been run first

        $binaries = $container->directory($appCodePath->appCodePath)->entries('vendor/bin');

        if (!in_array('phpstan', $binaries)) {
            $phpStanPhar = dag()->container()->from('ghcr.io/phpstan/phpstan')
                ->file('/composer/vendor/phpstan/phpstan/phpstan.phar');
            $container = $container->withFile('vendor/bin/phpstan', $phpStanPhar);
        }

        $container = $container->withExec(['vendor/bin/phpstan', '--no-progress'], expect: ReturnType::ANY);

        return new PHPStanReport($container->exitCode(), $container->stdout(), $container->stderr());
    }
}