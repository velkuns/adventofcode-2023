<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Struct;

use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;

readonly class Crucible implements \Stringable
{
    public function __construct(
        public Point2D $position,
        public Point2D|null $from,
        public Direction|null $direction,
        public int $move,
        public int $heatLoss
    ) {}

    public function __toString(): string
    {
        return "$this->position|{$this->direction?->value}|$this->move";
    }
}
