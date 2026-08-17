<?php

declare(strict_types=1);

namespace MaxServ\App\Filter;

use MaxServ\App\Utility\QueryUtility;
use Symfony\Component\HttpFoundation\Request;

final class ImportFilter extends AbstractFilter
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?array $statuses = null,
        public readonly ?string $startedFrom = null,
        public readonly ?string $startedTo = null,
        public readonly ?string $endedFrom = null,
        public readonly ?string $endedTo = null,
        public readonly ?string $search = null,
        public readonly ?string $sortBy = 'startedAt',
        public readonly ?string $sortDir = 'DESC',
    ) {
        parent::__construct();
    }

    protected const array SORTABLE_COLUMNS = [
        'id' => 'imports.id',
        'type' => 'imports.type',
        'startedAt' => 'imports.started_at',
        'endedAt' => 'imports.ended_at',
        'duration' => 'imports.duration_seconds',
    ];

    protected const array SEARCHABLE_COLUMNS = ['imports.id', 'imports.type', 'imports.status'];

    public static function fromRequest(Request $request): self
    {
        $statuses = $request->query->all(key: 'statuses');

        return new self(
            type: $request->query->get(key: 'type'),
            statuses: $statuses !== [] ? $statuses : null,
            startedFrom: $request->query->get(key: 'startedFrom'),
            startedTo: $request->query->get(key: 'startedTo'),
            endedFrom: $request->query->get(key: 'endedFrom'),
            endedTo: $request->query->get(key: 'endedTo'),
            search: $request->query->get(key: 'search'),
            sortBy: $request->query->getString(key: 'sortBy', default: 'startedAt'),
            sortDir: $request->query->getString(key: 'sortDir', default: 'DESC'),
        );
    }

    protected function filter(): void
    {
        $this->search(value: $this->search);

        if ($this->type !== null) {
            $this->where(sql: 'imports.type = :type', bindings: [':type' => $this->type]);
        }

        if ($this->statuses !== null) {
            [$placeholders, $bindings] = QueryUtility::buildNamedPlaceholders(prefix: 'status', values: $this->statuses);
            $this->where(sql: "imports.status IN ($placeholders)", bindings: $bindings);
        }

        if ($this->startedFrom !== null) {
            $this->where(sql: 'imports.started_at >= :startedFrom', bindings: [':startedFrom' => $this->startedFrom]);
        }

        if ($this->startedTo !== null) {
            $this->where(sql: 'imports.started_at <= :startedTo', bindings: [':startedTo' => $this->startedTo . ' 23:59:59']);
        }

        if ($this->endedFrom !== null) {
            $this->where(sql: 'imports.ended_at >= :endedFrom', bindings: [':endedFrom' => $this->endedFrom]);
        }

        if ($this->endedTo !== null) {
            $this->where(sql: 'imports.ended_at <= :endedTo', bindings: [':endedTo' => $this->endedTo . ' 23:59:59']);
        }

        $this->sort(column: $this->sortBy, direction: $this->sortDir);
    }
}
