<?php

declare(strict_types=1);

namespace DaggerModule;

final class Util
{
    public static function cmd(string $cmd): array
    {
        return ["/bin/sh", "-c", $cmd];
    }
}