<?php

declare(strict_types=1);

namespace MaxServ\Core\Mercure;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Mercure\Update;

final readonly class SafeHub implements HubInterface
{
    public function __construct(
        private HubInterface $hub,
    ) {}

    public function getUrl(): string
    {
        return $this->hub->getUrl();
    }

    public function getPublicUrl(): string
    {
        return $this->hub->getPublicUrl();
    }

    public function getProvider(): TokenProviderInterface
    {
        return $this->hub->getProvider();
    }

    public function getFactory(): ?TokenFactoryInterface
    {
        return $this->hub->getFactory();
    }

    public function publish(Update $update): string
    {
        return $this->hub->publish($update);
    }

    public function publishSafely(Update $update): ?string
    {
        try {
            return $this->hub->publish($update);
        } catch (\Throwable $exception) {
            // A Mercure hiccup is a lost UI notification, not a failure the caller should
            // have to handle — swallow and log instead of letting it propagate.
            error_log((string) $exception);
            return null;
        }
    }
}