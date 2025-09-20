<?php

declare(strict_types=1);

namespace Tests\Entities;

use MichaelRushton\ORM\Attributes\Column;
use MichaelRushton\ORM\Attributes\PrimaryKey;
use MichaelRushton\ORM\Attributes\Table;

#[Table(name: 'test')]
#[PrimaryKey(column: ['id1', 'id2'])]
class PrimaryKeyEntity
{
    #[Column]
    public int $id1;

    #[Column]
    public int $id2;
}
