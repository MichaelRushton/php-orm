<?php

declare(strict_types=1);

namespace MichaelRushton\ORM\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public readonly ?string $name = null
    ) {
    }
}
