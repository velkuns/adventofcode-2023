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

    public function maxRangeForValue(int $value): int
    {
        foreach ($this->ranges as $range) {
            //~ Seems to be before the first range, so get 0 as min range value
            if ($value < $range['source']['min']) {
                return $range['source']['min'] - 1;
            }

            //~ Value in range so get max of current range
            if ($value <= $range['source']['max']) {
                return $range['source']['max'];
            }
        }

        //~ Value is over registered ranges, so return current value as max
        return $value;
    }

    public function minRangeForMappedValue(int $value): int
    {
        //~ Sort ranges by destination min values
        $ranges = [];
        foreach ($this->ranges as $range) {
            $ranges[$range['destination']['min']] = $range;
        }

        ksort($ranges);

        foreach ($ranges as $range) {
            //~ Seems to be before the first range, so get 0 as min range value
            if ($value < $range['destination']['min']) {
                return $value;
            }

            //~ Value in range so get max of current range
            if ($value <= $range['destination']['max']) {
                return $range['source']['min'];
            }
        }

        //~ Value is over registered ranges, so return max of last range + 1
        return ($range['destination']['min'] ?? $value) + 1;
    }

    public function maxRangeForMappedValue(int $value): int
    {
        foreach ($this->ranges as $range) {
            //~ Seems to be before the first range, so get 0 as min range value
            if ($value < $range['destination']['min']) {
                return $range['destination']['min'] - 1;
            }

            //~ Value in range so get max of current range
            if ($value <= $range['destination']['max']) {
                return min($range['source']['max'], $value);
            }
        }

        //~ Value is over registered ranges, so return current value as max
        return ($range['destination']['max'] ?? $value) + 1;
    }

    public function merge(RangesMap $rangesMapB): RangesMap
    {
        $rangesMapMerged = new RangesMap();
        $rangesMapA      = $this;

        //~ Start by search min ranges in range map A
        foreach ($rangesMapA->ranges as $rangeA) {
            //~ Get min, max and length from current range of A
            $minA    = $rangeA['source']['min'];
            $maxA    = $rangeA['source']['max'];
            $lengthA = $maxA - $minA + 1;

            //~ Get min (min A mapped value), max (max range of min B value) and length in range B
            $minB    = $rangesMapA->map($minA);
            $maxB    = $rangesMapB->maxRangeForValue($minB);
            $lengthB = $maxB - $minB + 1;

            //~ Then get mapped value for min B
            $mapMinB = $rangesMapB->map($minB);
            echo "Range A: source min {$rangeA['source']['min']} => $mapMinB\n";

            //~ Register range in merged ranges
            $rangesMapMerged->add($minA, $mapMinB, min($lengthA, $lengthB));
        }

        //~ Then do the same for range map B
        foreach ($rangesMapB->ranges as $rangeB) {
            //~ Get min, max and length from current range of A
            $minA    = $this->minRangeForMappedValue($rangeB['source']['min']);
            $maxA    = $this->maxRangeForMappedValue($rangeB['source']['min']);
            $lengthA = $maxA - $minA + 1;

            echo "A: $minA | $maxA | $lengthA\n";

            //~ Get min (min A mapped value), max (max range of min B value) and length in range B
            $minB    = $rangeB['source']['min'];
            $maxB    = $rangeB['source']['max'];
            $lengthB = $maxB - $minB + 1;

            echo "B: $minB | $maxB | $lengthB\n";

            //~ Then get mapped value for min B
            $mapMinB = $rangesMapB->map($minB);
            echo "Range B: source min {$rangeB['source']['min']} => $mapMinB\n";

            //~ Register range in merged ranges
            $rangesMapMerged->add($minA, $mapMinB, min($lengthA, $lengthB));
        }

        return $rangesMapMerged;
    }

    public function sort(): static
    {
        ksort($this->ranges);

        return $this;
    }
}
