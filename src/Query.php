<?php

declare(strict_types=1);

namespace MichaelRushton\ORM;

use Generator;
use MichaelRushton\DB\Interfaces\ConnectionInterface;
use MichaelRushton\DB\Interfaces\SQL\Traits\HasBindings;
use MichaelRushton\DB\SQL\Traits\Bindings;
use MichaelRushton\DB\SQL\Traits\ForKeyShare;
use MichaelRushton\DB\SQL\Traits\ForNoKeyUpdate;
use MichaelRushton\DB\SQL\Traits\ForShare;
use MichaelRushton\DB\SQL\Traits\ForUpdate;
use MichaelRushton\DB\SQL\Traits\HighPriority;
use MichaelRushton\DB\SQL\Traits\Join;
use MichaelRushton\DB\SQL\Traits\Limit;
use MichaelRushton\DB\SQL\Traits\LockInShareMode;
use MichaelRushton\DB\SQL\Traits\OffsetFetch;
use MichaelRushton\DB\SQL\Traits\OrderBy;
use MichaelRushton\DB\SQL\Traits\RowsExamined;
use MichaelRushton\DB\SQL\Traits\SQLBigResult;
use MichaelRushton\DB\SQL\Traits\SQLBufferResult;
use MichaelRushton\DB\SQL\Traits\SQLCache;
use MichaelRushton\DB\SQL\Traits\SQLSmallResult;
use MichaelRushton\DB\SQL\Traits\StraightJoin;
use MichaelRushton\DB\SQL\Traits\Top;
use MichaelRushton\DB\SQL\Traits\Where;
use MichaelRushton\DB\SQL\Traits\Window;
use MichaelRushton\DB\SQL\Traits\With;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Interfaces\DataMapperInterface;
use MichaelRushton\ORM\Interfaces\QueryInterface;
use PDO;
use Stringable;

class Query implements QueryInterface, HasBindings, Stringable
{
    use Bindings;
    use ForKeyShare;
    use ForNoKeyUpdate;
    use ForShare;
    use ForUpdate;
    use HighPriority;
    use Join;
    use Limit;
    use LockInShareMode;
    use OffsetFetch;
    use OrderBy;
    use RowsExamined;
    use SQLBigResult;
    use SQLBufferResult;
    use SQLCache;
    use SQLSmallResult;
    use StraightJoin;
    use Top;
    use Where;
    use Window;
    use With;

    public readonly string $table;

    public function __construct(
        public readonly ConnectionInterface $connection,
        public readonly DataMapper $data_mapper
    )
    {
        $this->table = $data_mapper->table();
    }

    public function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function dataMapper(): DataMapperInterface
    {
        return $this->data_mapper;
    }

    public function fetch(): object|false
    {

        if (false === $row = $this->connection()->fetch("$this", $this->bindings(), PDO::FETCH_ASSOC)) {
            return false;
        }

        return $this->dataMapper()->create($row);

    }

    public function require(): object
    {

        if (!$entity = $this->fetch()) {
            throw new EntityNotFoundException();
        }

        return $entity;

    }

    public function fetchAll(): array|false
    {

        if (false === $rows = $this->connection()->fetchAll("$this", $this->bindings(), PDO::FETCH_ASSOC)) {
            return false;
        }

        foreach ($rows as $row) {
            $entities[] = $this->dataMapper()->create($row);
        }

        return $entities ?? [];

    }

    public function yield(): Generator {

        if ($stmt = $this->connection()->execute("$this", $this->bindings())) {

            while (false !== $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                yield $this->dataMapper()->create($row);
            }

        }

    }

    public function __toString(): string
    {

        $this->bindings = [];

        return implode(' ', array_filter([
            $this->getWith(),
            'SELECT',
            'DISTINCT',
            $this->high_priority,
            $this->straight_join,
            $this->sql_small_result,
            $this->sql_big_result,
            $this->sql_buffer_result,
            $this->sql_cache,
            $this->getTop(),
            $this->table . '.*',
            'FROM ' . $this->table,
            $this->getJoin(),
            $this->getWhere(),
            $this->getWindow(),
            $this->getOrderBy(),
            $this->getLimit() ?: $this->getOffsetFetch(),
            $this->getRowsExamined(),
            $this->getForUpdate(),
            $this->getForNoKeyUpdate(),
            $this->getForShare(),
            $this->getForKeyShare(),
            $this->lock_in_share_mode,
        ], '\strlen'));

    }
}
