<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;
use Dagger\Directory;
use Dagger\File;
use DaggerModule\Labels\AppCodePath;

#[DaggerObject]
final class NginxContainer
{
    public function __construct(private Container $baseContainer)
    {
    }

    #[DaggerFunction('Add application code to container')]
    public function withAppCode(Directory $appCode, string $path = '/app'): NginxContainer
    {
        $container = $this->baseContainer->withExec(Util::cmd('mkdir -p ' . $path))
            ->withExec(Util::cmd('chown nginx:nginx ' . $path))
            ->withDirectory($path, $appCode, owner: "nginx:nginx")
            ->withWorkdir($path)
            ->withLabel(AppCodePath::LABEL, (string) (new AppCodePath($path)));

        return new NginxContainer($container);
    }

    #[DaggerFunction('Add config template to the container')]
    public function withConfigTemplate(File $configTemplate): NginxContainer
    {
        $container = $this->baseContainer->withFile('/etc/nginx/templates/' . $configTemplate->name(), $configTemplate);

        return new NginxContainer($container);
    }

    #[DaggerFunction('Returns the built Nginx container')]
    public function container(): Container
    {
        return $this->baseContainer;
    }
}