<?php

declare(strict_types=1);

namespace MaxServ\App\Enum;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Started = 'started';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
