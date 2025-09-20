<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PrimaryKey
{
    public function __construct(
        public readonly string|array $column = 'id',
        public readonly bool $increments = true
    ) {
    }
}
