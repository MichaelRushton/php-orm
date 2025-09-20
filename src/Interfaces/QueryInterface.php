<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Interfaces;

use Generator;
use MichaelRushton\DB\Interfaces\ConnectionInterface;
use Stringable;

interface QueryInterface
{
    public function connection(): ConnectionInterface;

    public function dataMapper(): DataMapperInterface;

    public function with(
        string $name,
        string|Stringable|callable $stmt,
        ?callable $callback = null,
    ): static;

    public function recursive(): static;

    public function highPriority(): static;

    public function straightJoinAll(): static;

    public function sqlSmallResult(): static;

    public function sqlBigResult(): static;

    public function sqlBufferResult(): static;

    public function sqlCache(): static;

    public function sqlNoCache(): static;

    public function top(int|float|string|Stringable $row_count): static;

    public function percent(): static;

    public function withTies(): static;

    public function join(
        string|Stringable|array $table,
        string|Stringable|int|float|bool|null|array|callable $column1 = null,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $column2 = null
    ): static;

    public function leftJoin(
        string|Stringable|array $table,
        string|Stringable|int|float|bool|null|array|callable $column1 = null,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $column2 = null
    ): static;

    public function rightJoin(
        string|Stringable|array $table,
        string|Stringable|int|float|bool|null|array|callable $column1 = null,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $column2 = null
    ): static;

    public function fullJoin(
        string|Stringable|array $table,
        string|Stringable|int|float|bool|null|array|callable $column1 = null,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $column2 = null
    ): static;

    public function straightJoin(
        string|Stringable|array $table,
        string|Stringable|int|float|bool|null|array|callable $column1 = null,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $column2 = null
    ): static;

    public function crossJoin(string|Stringable|array $table): static;

    public function naturalJoin(string|Stringable|array $table): static;

    public function naturalLeftJoin(string|Stringable|array $table): static;

    public function naturalRightJoin(string|Stringable|array $table): static;

    public function naturalFullJoin(string|Stringable|array $table): static;

    public function where(
        string|Stringable|int|float|bool|array|callable $column,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $value = null
    ): static;

    public function orWhere(
        string|Stringable|int|float|bool|array|callable $column,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $value = null
    ): static;

    public function whereNot(
        string|Stringable|int|float|bool|array|callable $column,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $value = null
    ): static;

    public function orWhereNot(
        string|Stringable|int|float|bool|array|callable $column,
        string|Stringable|int|float|bool|null|array $operator = null,
        string|Stringable|int|float|bool|null|array $value = null
    ): static;

    public function whereIn(
        string|Stringable|int|float|bool $column,
        array $values
    ): static;

    public function orWhereIn(
        string|Stringable|int|float|bool $column,
        array $values
    ): static;

    public function whereNotIn(
        string|Stringable|int|float|bool $column,
        array $values
    ): static;

    public function orWhereNotIn(
        string|Stringable|int|float|bool $column,
        array $values
    ): static;

    public function whereBetween(
        string|Stringable|int|float $column,
        string|Stringable|int|float $value1,
        string|Stringable|int|float $value2
    ): static;

    public function orWhereBetween(
        string|Stringable|int|float $column,
        string|Stringable|int|float $value1,
        string|Stringable|int|float $value2
    ): static;

    public function whereNotBetween(
        string|Stringable|int|float $column,
        string|Stringable|int|float $value1,
        string|Stringable|int|float $value2
    ): static;

    public function orWhereNotBetween(
        string|Stringable|int|float $column,
        string|Stringable|int|float $value1,
        string|Stringable|int|float $value2
    ): static;

    public function whereNull(string|Stringable $column): static;

    public function orWhereNull(string|Stringable $column): static;

    public function whereNotNull(string|Stringable $column): static;

    public function orWhereNotNull(string|Stringable $column): static;

    public function window(
        string $name,
        ?callable $callback = null,
    ): static;

    public function orderBy(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function orderByDesc(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function orderByNullsFirst(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function orderByNullsLast(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function orderByDescNullsFirst(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function orderByDescNullsLast(
        string|Stringable|array $column,
        string|Stringable|array ...$columns
    ): static;

    public function limit(
        int|string|Stringable $row_count,
        int|string|Stringable|null $offset = null
    ): static;

    public function offsetFetch(
        int|string|Stringable $offset,
        int|string|Stringable $row_count
    ): static;

    public function rowsExamined(int|string|Stringable $row_count): static;

    public function forUpdate(string|array|null $table = null): static;

    public function forUpdateWait(int $seconds): static;

    public function forUpdateNoWait(string|array|null $table = null): static;

    public function forUpdateSkipLocked(string|array|null $table = null): static;

    public function forNoKeyUpdate(string|array|null $table = null): static;

    public function forNoKeyUpdateNoWait(string|array|null $table = null): static;

    public function forNoKeyUpdateSkipLocked(string|array|null $table = null): static;

    public function forShare(string|array|null $table = null): static;

    public function forShareNoWait(string|array|null $table = null): static;

    public function forShareSkipLocked(string|array|null $table = null): static;

    public function forKeyShare(string|array|null $table = null): static;

    public function forKeyShareNoWait(string|array|null $table = null): static;

    public function forKeyShareSkipLocked(string|array|null $table = null): static;

    public function lockInShareMode(): static;

    public function lockInShareModeWait(int $seconds): static;

    public function lockInShareModeNoWait(): static;

    public function lockInShareModeSkipLocked(): static;

    public function fetch(): object|false;

    public function fetchAll(): array|false;

    public function yield(): Generator;
}
