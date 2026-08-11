<?php

declare(strict_types=1);

namespace MaxServ\Core\Container;

use MaxServ\Core\Cache\CacheableInterface;
use MaxServ\Core\DependencyInjection\Compiler\TwigPathsPass;
use MaxServ\Core\Twig\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final readonly class ContainerConfigurator
{
    public static function configure(ContainerBuilder $container): void
    {
        self::registerParameters(container: $container);
        self::registerAttributeConfigurators(container: $container);
        self::registerAutoconfiguration(container: $container);
        self::registerCompilerPasses(container: $container);
    }

    private static function registerParameters(ContainerBuilder $container): void
    {
        $container->setParameter(name: 'application.root', value: APPLICATION_ROOT);

        $container->setParameter(name: 'db.host', value: '%env(DB_HOST)%');
        $container->setParameter(name: 'db.user', value: '%env(DB_USER)%');
        $container->setParameter(name: 'db.password', value: '%env(DB_PASSWORD)%');
        $container->setParameter(name: 'db.database', value: '%env(DB_DATABASE)%');

        $container->setParameter(name: 'mercure.hub_url', value: '%env(MERCURE_HUB_URL)%');
        $container->setParameter(name: 'mercure.publisher_jwt', value: '%env(MERCURE_PUBLISHER_JWT)%');
        $container->setParameter(name: 'mercure.public_url', value: '%env(MERCURE_PUBLIC_URL)%');

        $container->setParameter(name: 'app.debug', value: '%env(bool:APP_DEBUG)%');
        $container->setParameter(name: 'app.is_dev', value: '%env(is_dev:APP_ENV)%');
        $container->setParameter(name: 'app.url', value: '%env(APP_URL)%');

        $container->setParameter(name: 'vite.server_url', value: '%env(VITE_SERVER_URL)%');
        $container->setParameter(name: 'vite.base_path', value: '/assets/');
    }

    private static function registerAttributeConfigurators(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            attributeClass: AsMessageHandler::class,
            configurator: static fn(ChildDefinition $definition)
                => $definition->addTag(name: 'messenger.message_handler'),
        );
        $container->registerAttributeForAutoconfiguration(
            attributeClass: AsCommand::class,
            configurator: static fn(ChildDefinition $definition)
                => $definition->addTag(name: 'console.command'),
        );
    }

    private static function registerAutoconfiguration(ContainerBuilder $container): void
    {
        $container
            ->registerForAutoconfiguration(interface: EnvVarProcessorInterface::class)
            ->addTag(name: 'container.env_var_processor');

        $container
            ->registerForAutoconfiguration(interface: CacheableInterface::class)
            ->addTag(name: 'app.cacheable');

        $container
            ->registerForAutoconfiguration(interface: ErrorRendererInterface::class)
            ->addTag(name: 'core.error_renderer');

        $container
            ->registerForAutoconfiguration(interface: EventSubscriberInterface::class)
            ->addTag(name: 'kernel.event_subscriber');
    }

    private static function registerCompilerPasses(ContainerBuilder $container): void
    {
        $container->addCompilerPass(pass: new AddConsoleCommandPass());
        $container->addCompilerPass(pass: new TwigPathsPass());
        $container->addCompilerPass(pass: new RegisterListenersPass());
        $container->addCompilerPass(pass: new MessengerPass());
    }
}
