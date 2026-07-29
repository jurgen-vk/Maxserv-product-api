<?php

declare(strict_types=1);

namespace MaxServ\Core\Notification;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final readonly class Notifications
{
    private const string KEY = 'notifications';

    public function __construct(
        private RequestStack $requestStack,
    ) {}

    public function add(string $message, string $type = 'default', ?string $action = null, ?int $duration = null): void
    {
        $this->flashBag()?->add(type: self::KEY, message: [
            'message' => $message,
            'type' => $type,
            'action' => $action,
            'duration' => $duration,
        ]);
    }

    public function set(array $notifications): void
    {
        $this->flashBag()?->set(type: self::KEY, messages: $notifications);
    }

    public function setAll(array $notifications): void
    {
        $this->set(notifications: $notifications);
    }

    /** @return list<array{message: string, type: string, action: ?string, duration: ?int}> */
    public function peek(?string $type = null, ?string $action = null): array
    {
        return $this->matching(
            notifications: $this->flashBag()?->peek(type: self::KEY, default: []) ?? [],
            type: $type,
            action: $action,
        );
    }

    /** @return list<array{message: string, type: string, action: ?string, duration: ?int}> */
    public function peekAll(): array
    {
        return $this->peek();
    }

    /** @return list<array{message: string, type: string, action: ?string, duration: ?int}> */
    public function get(?string $type = null, ?string $action = null): array
    {
        $notifications = $this->flashBag()?->get(type: self::KEY, default: []) ?? [];

        $matching = $this->matching(notifications: $notifications, type: $type, action: $action);
        $remaining = $this->matching(notifications: $notifications, type: $type, action: $action, invert: true);

        if ($remaining !== []) {
            $this->set(notifications: $remaining);
        }

        return $matching;
    }

    /** @return list<array{message: string, type: string, action: ?string, duration: ?int}> */
    public function all(): array
    {
        return $this->flashBag()?->get(type: self::KEY, default: []) ?? [];
    }

    public function has(?string $type = null, ?string $action = null): bool
    {
        return $this->peek(type: $type, action: $action) !== [];
    }

    /** @return list<string> */
    public function keys(string $for = 'type'): array
    {
        $values = array_map(
            callback: static fn(array $notification) => $notification[$for] ?? null,
            array: $this->peek(),
        );

        return array_values(array: array_unique(array: array_filter(array: $values)));
    }

    private function matching(array $notifications, ?string $type, ?string $action, bool $invert = false): array
    {
        return array_values(array: array_filter(
            array: $notifications,
            callback: function (array $notification) use ($type, $action, $invert): bool {
                $matches = $this->matches(stored: $notification['type'], filter: $type)
                    && $this->matches(stored: $notification['action'], filter: $action);

                return $invert ? !$matches : $matches;
            },
        ));
    }

    private function matches(?string $stored, ?string $filter): bool
    {
        return match (true) {
            $filter === null => true,
            $filter === '' => $stored === null || $stored === '',
            default => $stored === $filter,
        };
    }

    private function flashBag(): ?FlashBagInterface
    {
        return $this->requestStack->getCurrentRequest()?->getSession()->getFlashBag();
    }
}