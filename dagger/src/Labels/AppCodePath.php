<?php

declare(strict_types=1);

namespace DaggerModule\Labels;

use Dagger\Container;

final class AppCodePath implements \JsonSerializable
{
    use Label;
    const LABEL = 'sai.appCodePath';

    public function __construct(public string $appCodePath)
    {

    }
}