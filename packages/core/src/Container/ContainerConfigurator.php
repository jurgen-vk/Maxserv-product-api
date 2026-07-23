<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use MaxServ\Core\DependencyInjection\Compiler\TwigPathsPass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class ContainerConfigurator
{
    public static function configure(ContainerBuilder $container): void
    {
        self::registerParameters($container);
        self::registerAttributeConfigurators($container);
        self::registerCompilerPasses($container);
    }

    private static function registerParameters(ContainerBuilder $container): void
    {
        $container->setParameter('application.root', APPLICATION_ROOT);

        $container->setParameter('db.host', '%env(DB_HOST)%');
        $container->setParameter('db.user', '%env(DB_USER)%');
        $container->setParameter('db.password', '%env(DB_PASSWORD)%');
        $container->setParameter('db.database', '%env(DB_DATABASE)%');

        $container->setParameter('mercure.hub_url', '%env(MERCURE_HUB_URL)%');
        $container->setParameter('mercure.publisher_jwt', '%env(MERCURE_PUBLISHER_JWT)%');
        $container->setParameter('mercure.public_url', '%env(MERCURE_PUBLIC_URL)%');

        $container->setParameter('app.debug', '%env(bool:APP_DEBUG)%');
        $container->setParameter('app.is_dev', '%env(is_dev:APP_ENV)%');
        $container->setParameter('app.url', '%env(APP_URL)%');

        $container->setParameter('vite.server_url', '%env(VITE_SERVER_URL)%');
    }

    private static function registerAttributeConfigurators(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            AsMessageHandler::class,
            static fn (ChildDefinition $definition) => $definition->addTag('messenger.message_handler'),
        );
        $container->registerAttributeForAutoconfiguration(
            AsCommand::class,
            static fn (ChildDefinition $definition) => $definition->addTag('console.command'),
        );
    }

    private static function registerCompilerPasses(ContainerBuilder $container): void
    {
        // Real Symfony's own pass (vendored with symfony/console) — registers a lazy
        // ContainerCommandLoader instead of eagerly instantiating every command just to
        // populate Application's command list. Building/warming a container must not force
        // every command's full dependency graph (e.g. Connection, Mercure) into existence.
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new TwigPathsPass());
        $container->addCompilerPass(new RegisterListenersPass());
        $container->addCompilerPass(new MessengerPass());
    }
}
