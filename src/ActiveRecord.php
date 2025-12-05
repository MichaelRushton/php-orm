<?php

declare(strict_types=1);

namespace MichaelRushton\ORM;

use Generator;
use MichaelRushton\ORM\Interfaces\ActiveRecordInterface;
use MichaelRushton\ORM\Interfaces\EntityManagerInterface;
use MichaelRushton\ORM\Exceptions\EntityNotFoundException;
use MichaelRushton\ORM\Interfaces\QueryInterface;
use PDOStatement;

abstract class ActiveRecord implements ActiveRecordInterface
{
    protected static ?EntityManagerInterface $entity_manager = null;

    public function __construct(
        protected array $attributes = []
    ) {
    }

    public static function setEntityManager(EntityManagerInterface $entity_manager): void
    {
        static::$entity_manager = $entity_manager;
    }

    public static function getEntityManager(): ?EntityManagerInterface
    {
        return static::$entity_manager;
    }

    public static function table(): string
    {
        return static::$entity_manager->table(static::class);
    }

    public static function primaryKey(): string|array
    {
        return static::$entity_manager->primaryKey(static::class);
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function mergeAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public static function fetch(int|string|array|callable $where = []): static|false
    {
        return static::$entity_manager->fetch(static::class, $where);
    }

    public static function require(int|string|array|callable $where = []): static
    {

        if (!$entity = static::fetch($where)) {
            throw new EntityNotFoundException();
        }

        return $entity;

    }

    public static function fetchAll(array|callable $where = []): array|false
    {
        return static::$entity_manager->fetchAll(static::class, $where);
    }

    public static function yield(array|callable $where = []): Generator
    {
        return static::$entity_manager->yield(static::class, $where);
    }

    public function delete(): PDOStatement|false
    {
        return static::$entity_manager->delete($this);
    }

    public function insert(array $attributes = []): PDOStatement|false
    {

        $this->attributes = array_merge($this->attributes, $attributes);

        return static::$entity_manager->insert($this);

    }

    public static function create(array $attributes = []): static
    {

        $entity = new static();

        $entity->insert($attributes);

        return $entity;

    }

    public function update(array $attributes = []): PDOStatement|false
    {

        $this->attributes = array_merge($this->attributes, $attributes);

        return static::$entity_manager->update($this);

    }

    public static function query(): QueryInterface
    {
        return static::$entity_manager->query(static::class);
    }

    public function __set(
        string $name,
        mixed $value
    ): void {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }
}
