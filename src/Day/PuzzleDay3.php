<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Puzzle;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\Matrix;

class PuzzleDay3 extends Puzzle
{
    private const DIRECTIONS = [
        //~ First pos only
        'for_first' => [
            'topLeft'     => [-1, -1],
            'left'        => [-1, 0],
            'bottomLeft'  => [-1, 1],
        ],
        //~ All pos
        'for_all' => [
            'bottom'      => [0, 1],
            'top'         => [0, -1],
        ],
        //~ Last number
        'for_last' => [
            'topRight'    => [1, -1],
            'right'       => [1, 0],
            'bottomRight' => [1, 1]
        ],
    ];

    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $engineSchematic = (new Matrix(\array_map(\str_split(...), $inputs)))->transpose();

        return \array_sum($this->getPartNumbers($engineSchematic));
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $engineSchematic = (new Matrix(\array_map(\str_split(...), $inputs)))->transpose();

        return \array_sum($this->getGearRatios($engineSchematic));
    }

    /**
     * @return list<int>
     */
    public function getPartNumbers(Matrix $engineSchematic): array
    {
        $partNumbers = [];
        foreach ($this->nextNumber($engineSchematic) as $number => $positions) {
            if ($this->getAdjacentSymbol($engineSchematic, $positions, null, $number) !== null) {
                $partNumbers[] = $number;
            }
        }

        return $partNumbers;
    }

    /**
     * @return list<int>
     */
    public function getGearRatios(Matrix $engineSchematic): array
    {
        $possibleGears = [];
        foreach ($this->nextNumber($engineSchematic) as $number => $positions) {
            $possibleGear = $this->getAdjacentSymbol($engineSchematic, $positions, '*', $number);
            if ($possibleGear === null) {
                continue;
            }
            $possibleGears[(string) $possibleGear][] = $number;
        }

        $gearRatios = [];
        foreach ($possibleGears as $numbers) {
            if (\count($numbers) === 2) {
                $gearRatios[] = (int) \array_product($numbers);
            }
        }

        return $gearRatios;
    }

    /**
     * @return \Generator<int, list<Point2D>>
     */
    public function nextNumber(Matrix $engineSchematic): \Generator
    {
        $number    = '';
        $positions = [];

        for ($y = 0; $y < $engineSchematic->height(); $y++) {
            for ($x = 0; $x < $engineSchematic->width(); $x++) {
                $pos  = new Point2D($x, $y);
                /** @var string $char */
                $char = $engineSchematic->get($pos);

                //~ Next char is not numeric & already have number, return current data
                if ($number !== '' && !\is_numeric($char)) {
                    yield (int) $number => $positions;
                    $number    = '';
                    $positions = [];
                    continue;
                }

                //~ Next char is not numeric (and implicitly have no current number), continue
                if (!\is_numeric($char)) {
                    continue;
                }

                //~ Next char is numeric & is part of current number
                $number     .= $char;
                $positions[] = $pos;
            }

            if ($number !== '') {
                yield (int) $number => $positions;
            }
            $number    = '';
            $positions = [];
        }
    }

    /**
     * @param Matrix $engineSchematic
     * @param list<Point2D> $positions
     * @param string|null $symbol
     * @param int $number
     * @return Point2D
     */
    private function getAdjacentSymbol(
        Matrix $engineSchematic,
        array $positions,
        string|null $symbol,
        int $number
    ): Point2D|null {
        $isFirst = true;
        foreach ($positions as $index => $position) {
            //echo "--- check for #$index of number $number ({$position->getX()}, {$position->getY()})---\n";
            $isLast = !isset($positions[($index + 1)]);

            $checkAt = self::DIRECTIONS['for_all'];

            if ($isFirst) {
                $checkAt = array_merge($checkAt, self::DIRECTIONS['for_first']);
            }

            if ($isLast) {
                $checkAt = array_merge($checkAt, self::DIRECTIONS['for_last']);
            }

            foreach ($checkAt as [$x, $y]) {
                $point = new Point2D($position->getX() + $x, $position->getY() + $y);
                /** @var string|null $char */
                $char = $engineSchematic->get($point);
                if (
                    ($symbol !== null && $char === $symbol) ||
                    ($symbol === null && $char !== '.' && !\is_numeric($char) && $char !== null)
                ) {
                    return $point;
                }
            }

            $isFirst = false;
        }

        return null;
    }
}
