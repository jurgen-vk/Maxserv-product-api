<?php

declare(strict_types=1);

namespace MaxServ\Core\Time;

use Symfony\Component\HttpFoundation\RequestStack;

final class Timezone extends \DateTimeZone
{
    public function __construct(RequestStack $requestStack)
    {
        $cookie = $requestStack->getCurrentRequest()?->cookies->get(key: 'timezone');

        try {
            parent::__construct($cookie ?? 'UTC');
        } catch (\Exception) {
            parent::__construct('UTC');
        }
    }
}