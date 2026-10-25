<?php

declare(strict_types=1);

namespace DaggerModule\Labels;

final class NginxContainerInfo implements \JsonSerializable
{
    use Label;
    public const LABEL = 'sai.nginx.containerInfo';

    public function __construct(
        public string $version,
        public string $os,
        public ?string $variant,
    ) {
        $this->validateOS($this->os);
    }

    public function getTag(): string
    {
        $tag = sprintf('nginx:%s', $this->version);

        if ($this->os !== 'debian') {
            $tag .= sprintf('-%s', $this->os);
        }

        if ($this->variant !== null) {
            $tag .= sprintf('-%s', $this->variant);
        }

        return $tag;
    }

    private function validateOS(string $os): void
    {
        match ($os) {
            'debian', 'bookworm', 'trixie', 'alpine', 'alpine3.23', 'alpine3.24' => null,
            default => throw new \InvalidArgumentException('Invalid option for OS argument')
        };
    }
}