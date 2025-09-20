<?php

declare(strict_types=1);

namespace MichaelRushton\ORM;

use MichaelRushton\ORM\Attributes\Column;
use MichaelRushton\ORM\Attributes\PrimaryKey;
use MichaelRushton\ORM\Attributes\Table;
use MichaelRushton\ORM\Interfaces\ActiveRecordInterface;
use MichaelRushton\ORM\Interfaces\DataMapperInterface;
use MichaelRushton\ORM\Exceptions\EntityMissingTableAttributeException;
use MichaelRushton\ORM\Exceptions\InvalidEntityException;
use ReflectionAttribute;
use ReflectionClass;

class DataMapper implements DataMapperInterface
{
    public readonly ReflectionClass $reflection_class;
    protected string $table;
    protected string|array $primary_key;
    protected bool $increments;
    protected array $columns;

    public function __construct(public readonly string $class)
    {
        $this->reflection_class = new ReflectionClass($class);
    }

    public function attribute(string $name): ?ReflectionAttribute
    {
        return $this->reflection_class->getAttributes($name)[0] ?? null;
    }

    public function arguments(string $attribute): array
    {
        return $this->attribute($attribute)?->getArguments() ?? [];
    }

    public function table(): string
    {

        $this->table ??= $this->arguments(Table::class)['name'] ?? '';

        if ('' === $this->table) {
            throw new EntityMissingTableAttributeException($this->class);
        }

        return $this->table;

    }

    public function primaryKey(): string|array
    {
        return $this->primary_key ??= $this->arguments(PrimaryKey::class)['column'] ?? 'id';
    }

    public function increments(): bool
    {
        return $this->increments ??= \is_string($this->primaryKey()) && ($this->arguments(PrimaryKey::class)['increments'] ?? true);
    }

    public function columns(): array
    {

        if (isset($this->columns)) {
            return $this->columns;
        }

        foreach ($this->reflection_class->getProperties() as $property) {

            if ($column = $property->getAttributes(Column::class)[0] ?? null) {
                $columns[$column->getArguments()['name'] ?? $property->getName()] = [$property, $column];
            }

        }

        return $this->columns = $columns ?? [];

    }

    protected function throwExceptionIfInvalidEntity(object $entity): void
    {

        if (\get_class($entity) !== $this->class) {

            throw new InvalidEntityException(\sprintf(
                '%s entity is not an instance of %s',
                $entity::class,
                $this->class
            ));

        }

    }

    public function mergeAttributes(
        object $entity,
        array $attributes
    ): void {

        $this->throwExceptionIfInvalidEntity($entity);

        if ($entity instanceof ActiveRecordInterface) {
            $entity->mergeAttributes($attributes);
        } else {

            foreach ($this->columns() as $name => [$property, $column]) {

                if (\array_key_exists($name, $attributes)) {
                    $property->setValue($entity, $attributes[$name]);
                }

            }

        }

    }

    public function getAttributes(object $entity): array
    {

        $this->throwExceptionIfInvalidEntity($entity);

        if ($entity instanceof ActiveRecordInterface) {
            return $entity->getAttributes();
        }

        foreach ($this->columns() as $name => [$property, $column]) {

            if ($property->isInitialized($entity)) {
                $attributes[$name] = $property->getValue($entity);
            }

        }

        return $attributes ?? [];

    }

    public function create(array $attributes = []): object
    {

        $this->mergeAttributes($entity = new $this->class(), $attributes);

        return $entity;

    }
}
