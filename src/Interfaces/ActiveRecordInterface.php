<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Interfaces;

use Generator;
use PDOStatement;

interface ActiveRecordInterface
{
    public static function setEntityManager(EntityManagerInterface $entity_manager): void;

    public static function getEntityManager(): ?EntityManagerInterface;

    public static function table(): string;

    public static function primaryKey(): string|array;

    public function setAttributes(array $attributes): void;

    public function mergeAttributes(array $attributes): void;

    public function getAttributes(): array;

    public static function fetch(int|string|array|callable $where = []): static|false;

    public static function require(int|string|array|callable $where = []): static;

    public static function fetchAll(array|callable $where = []): array|false;

    public static function yield(array|callable $where = []): Generator;

    public function delete(): PDOStatement|false;

    public function insert(array $attributes = []): PDOStatement|false;

    public static function create(array $attributes = []): static;

    public function update(array $attributes = []): PDOStatement|false;

    public static function query(): QueryInterface;
}
