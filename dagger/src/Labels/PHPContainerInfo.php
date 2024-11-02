<?php

declare(strict_types=1);

namespace DaggerModule\Labels;

use Dagger\Container;

final class PHPContainerInfo implements \JsonSerializable
{
    use Label;
    public const LABEL = 'sai.php.containerInfo';

    public function __construct(
        public string $version,
        public string $os,
        public string $variant,
        public bool $hasComposer = false,
        public bool $hasExtensionInstaller = false,
        public array $extensions = []
    ) {
        $this->validateOS($this->os);
    }

    public function getTag(): string
    {
        return sprintf('php:%s-%s-%s', $this->version, $this->variant, $this->os);
    }

    private function validateOS(string $os): void
    {
        match ($os) {
            'debian', 'alpine' => null,
            default => throw new \InvalidArgumentException('Invalid option for OS argument')
        };
    }
}