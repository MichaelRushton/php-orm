<?php

declare(strict_types=1);

namespace MichaelRushton\ORM;

use Generator;
use MichaelRushton\DB\Interfaces\ConnectionInterface;
use MichaelRushton\ORM\Interfaces\DataMapperInterface;
use MichaelRushton\ORM\Interfaces\EntityManagerInterface;
use MichaelRushton\ORM\Exceptions\EntityMissingPrimaryKeyValueException;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Interfaces\QueryInterface;
use PDO;
use PDOStatement;

class EntityManager implements EntityManagerInterface
{
    protected static array $data_mappers = [];

    public function __construct(
        public readonly ConnectionInterface $connection
    ) {
    }

    public function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    public static function dataMapper(string $class): DataMapperInterface
    {
        return static::$data_mappers[$class] ??= new DataMapper($class);
    }

    public function table(string $class): string
    {
        return static::dataMapper($class)->table();
    }

    public function primaryKey(string $class): string|array
    {
        return static::dataMapper($class)->primaryKey();
    }

    public function fetch(
        string $class,
        int|string|array|callable $where = []
    ): object|false {

        $data_mapper = static::dataMapper($class);

        if (false === $row = $this->connection->select()
        ->from($data_mapper->table())
        ->where(\is_scalar($where) ? [$data_mapper->primaryKey() => $where] : $where)
        ->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        return $data_mapper->create($row);

    }

    public function require(
        string $class,
        int|string|array|callable $where = []
    ): object {

        if (!$entity = $this->fetch($class, $where)) {
            throw new EntityNotFoundException();
        }

        return $entity;

    }

    public function fetchAll(
        string $class,
        array|callable $where = []
    ): array|false {

        $data_mapper = static::dataMapper($class);

        if (false === $rows = $this->connection->select()
        ->from($data_mapper->table())
        ->where($where)
        ->fetchAll(PDO::FETCH_ASSOC)) {
            return false;
        }

        foreach ($rows as $row) {
            $entities[] = $data_mapper->create($row);
        }

        return $entities ?? [];

    }

    public function yield(
        string $class,
        array|callable $where = []
    ): Generator {

        $data_mapper = static::dataMapper($class);

        if (false === $stmt = $this->connection->select()
        ->from($data_mapper->table())
        ->where($where)
        ->execute()) {
            return false;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $data_mapper->create($row);
        }

    }

    protected function where(
        DataMapperInterface $data_mapper,
        array $attributes
    ): array {

        $where = array_intersect_key(
            $attributes,
            $primary_key = array_flip((array) $data_mapper->primaryKey()),
        );

        if (empty($where) || \count($where) !== \count($primary_key)) {

            throw new EntityMissingPrimaryKeyValueException(\sprintf(
                '%s: %s',
                $data_mapper->class,
                implode(', ', array_keys(array_diff_key($primary_key, $where)))
            ));

        }

        return $where;

    }

    public function delete(object $entity): PDOStatement|false
    {

        $data_mapper = static::dataMapper($entity::class);

        return $this->connection->delete()
        ->from($data_mapper->table())
        ->where($this->where(
            $data_mapper,
            $data_mapper->getAttributes($entity)
        ))
        ->execute();

    }

    public function insert(object $entity): PDOStatement|false
    {

        $data_mapper = static::dataMapper($entity::class);

        if (false === $stmt = $this->connection->insert()
        ->into($data_mapper->table())
        ->values($attributes = $data_mapper->getAttributes($entity))
        ->execute()) {
            return false;
        }

        $primary_key = $data_mapper->primaryKey();

        if (\is_string($primary_key) && $data_mapper->increments() && !isset($attributes[$primary_key])) {
            $data_mapper->mergeAttributes($entity, [
                $primary_key => (int) $this->connection->pdo()->lastInsertId()
            ]);
        }

        return $stmt;

    }

    public function update(object $entity): PDOStatement|false
    {

        $data_mapper = static::dataMapper($entity::class);

        return $this->connection->update()
        ->table($data_mapper->table())
        ->where($where = $this->where(
            $data_mapper,
            $attributes = $data_mapper->getAttributes($entity)
        ))
        ->set(array_diff_key($attributes, $where) ?: $where)
        ->execute();

    }

    public function query(string $class): QueryInterface
    {
        return new Query($this->connection(), static::dataMapper($class));
    }
}
