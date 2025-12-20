<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    public static function build(): ContainerInterface
    {
        $builder = new ContainerBuilder();
        $definitions = require __DIR__ . '/../../../config/container.php';
        $builder->addDefinitions($definitions);

        return $builder->build();
    }
}
