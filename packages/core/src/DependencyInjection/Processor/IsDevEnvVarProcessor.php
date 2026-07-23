<?php

declare(strict_types=1);

namespace MaxServ\Core\DependencyInjection\Processor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final class IsDevEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): bool
    {
        return $getEnv($name) !== 'prod';
    }

    public static function getProvidedTypes(): array
    {
        return ['is_dev' => 'bool'];
    }
}
