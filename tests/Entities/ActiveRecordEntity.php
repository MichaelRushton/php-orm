<?php

declare(strict_types=1);

namespace Tests\Entities;

use MichaelRushton\ORM\ActiveRecord;
use MichaelRushton\ORM\Attributes\Table;

#[Table(name: 'test')]
class ActiveRecordEntity extends ActiveRecord
{
}
