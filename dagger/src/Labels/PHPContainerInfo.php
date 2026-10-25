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
        $tag = sprintf('php:%s-%s', $this->version, $this->variant);

        if ($this->os !== 'debian') {
            $tag .= sprintf('-%s', $this->os);
        }

        return $tag;
    }

    private function validateOS(string $os): void
    {
        match ($os) {
            'debian', 'bookworm', 'trixie', 'alpine', 'alpine3.23', 'alpine3.24'  => null,
            default => throw new \InvalidArgumentException('Invalid option for OS argument')
        };
    }
}