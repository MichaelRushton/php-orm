<?php

declare(strict_types=1);

namespace Tests\Entities;

use MichaelRushton\ORM\Attributes\Column;
use MichaelRushton\ORM\Attributes\PrimaryKey;
use MichaelRushton\ORM\Attributes\Table;

#[Table(name: 'test')]
#[PrimaryKey(increments: false)]
class NoIncrementsEntity
{
    #[Column]
    public int $id;
}
