<?php

declare(strict_types=1);

namespace DaggerModule\Labels;

use Dagger\Container;

trait Label
{
    public static function fromContainer(Container $container): self
    {
        $json = $container->label(static::LABEL);

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Could not parse Sai Label from container, was this container created by Sai?');
        }

        return new self(...$data);
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function jsonSerialize(): mixed
    {
        return (array) $this;
    }
}