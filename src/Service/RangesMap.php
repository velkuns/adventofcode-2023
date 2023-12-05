<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

class RangesMap
{
    /** @var array<int, array{source: array{min: int, max: int}, destination: array{min: int, max: int}}> $ranges */
    protected array $ranges = [];

    public function add(
        int $sourceMin,
        int $destinationMin,
        int $length,
    ): static {

        $this->ranges[$sourceMin] = [
            'source'      => ['min' => $sourceMin, 'max' => $sourceMin + ($length - 1)],
            'destination' => ['min' => $destinationMin, 'max' => $destinationMin + ($length - 1)],
        ];

        return $this;
    }

    public function map(int $value): int
    {
        foreach ($this->ranges as $range) {
            if ($value >= $range['source']['min'] && $value <= $range['source']['max']) {
                $diff = $value - $range['source']['min'];
                return $range['destination']['min'] + $diff;
            }
        }

        return $value;
    }

    public function sort(): static
    {
        ksort($this->ranges);

        return $this;
    }
}
