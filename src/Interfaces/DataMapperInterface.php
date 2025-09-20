<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Interfaces;

interface DataMapperInterface
{
    public function table(): string;

    public function primaryKey(): string|array;

    public function increments(): bool;

    public function columns(): array;

    public function mergeAttributes(
        object $entity,
        array $attributes
    ): void;

    public function getAttributes(object $entity): array;

    public function create(array $attributes = []): object;
}
