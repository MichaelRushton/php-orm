<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
