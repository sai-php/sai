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
        $os = $this->os;
        if ($this->os === 'debian') {
            $os = 'bookworm';
        }

        if ($this->variant === null) {
            return sprintf('nginx:%s-%s', $this->version, $os);
        }

        return sprintf('nginx:%s-%s-%s', $this->version, $this->variant, $os);
    }

    private function validateOS(string $os): void
    {
        match ($os) {
            'debian', 'alpine', 'bookworm' => null,
            default => throw new \InvalidArgumentException('Invalid option for OS argument')
        };
    }
}