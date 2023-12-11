<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

use Application\Enum\PathDirection;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2D;
use Velkuns\Math\_2D\Vector2DDir;
use Velkuns\Math\Matrix;

class Universe extends Matrix
{
    public const GALAXY = '#';
    private int $expansion = 0;

    /** @var list<int> $expansionZonesOnX */
    private array $expansionZonesOnX = [];

    /** @var list<int> $expansionZonesOnY */
    private array $expansionZonesOnY = [];

    /**
     * Use real expansion of map is faster for part 3 (~160ms against ~380ms for simulated expansion).
     * But not usable for part 2 (it will take too much memory).
     */
    public function realExpand(bool $doExpandAgain = true): static
    {
        $matrix = [];
        foreach ($this->matrix as $line) {
            $matrix[] = $line;
            if (!in_array(self::GALAXY, $line)) {
                $matrix[] = $line;
            }
        }

        $universe = (new static($matrix));
        if ($doExpandAgain) {
            $universe = $universe->transpose()->realExpand(false);
        }

        return $universe;
    }

    public function expand(int $expansion): static
    {
        $expansionZonesOnX = [];
        $expansionZonesOnY = [];

        //~ For each line, search of zone without galaxy (that will expansion zone on Y)
        foreach ($this->matrix as $y => $line) {
            if (!in_array(self::GALAXY, $line)) {
                $expansionZonesOnY[] = $y;
            }
        }

        //~ Transpose matrix, then for each line, search of zone without galaxy (that will expansion zone on X)
        $universe = $this->transpose();
        foreach ($universe->matrix as $x => $column) {
            if (!in_array(self::GALAXY, $column)) {
                $expansionZonesOnX[] = $x;
            }
        }

        //~ Keep expansion zones in transposed final universe + expansion value
        $universe->expansionZonesOnX = $expansionZonesOnX;
        $universe->expansionZonesOnY = $expansionZonesOnY;

        //~ Remove 1 because I use addition instead multiplication for expansion (original size + expansion time - 1)
        //- 2 times larger         => 1 original size + (2 times larger - 1)        = 2 (size of the expansion zone)
        //- 1 million times larger => 1 original size + (1 million times large - 1) = 1 000 000 (size of the expansion zone)
        $universe->expansion = $expansion - 1;

        return $universe;
    }

    public function calculateDistanceBetweenTwoGalaxies(
        Point2D $galaxyA,
        Point2D $galaxyB,
        bool $useRealExpansion,
    ): int {
        $distance = (new Vector2D($galaxyA, $galaxyB))->manhattanDistance();

        if ($useRealExpansion) {
            return $distance;
        }

        //~ Pre-calculate min x/y & max x/y of galaxies to reduce calculation time by 3 rather than complex conditions
        $minX = min($galaxyA->getX(), $galaxyB->getX());
        $maxX = max($galaxyA->getX(), $galaxyB->getX());
        $minY = min($galaxyA->getY(), $galaxyB->getY());
        $maxY = max($galaxyA->getY(), $galaxyB->getY());

        //~ Search for expansion zone between the galaxies (on x) and add the expansion value for each zone found
        foreach ($this->expansionZonesOnX as $x) {
            if ($minX < $x && $maxX > $x) {
                $distance += $this->expansion;
            }
        }

        //~ Search for expansion zone between the galaxies (on y) and add the expansion value for each zone found
        foreach ($this->expansionZonesOnY as $y) {
            if ($minY < $y && $maxY > $y) {
                $distance += $this->expansion;
            }
        }

        return $distance;
    }

    public function render(): string
    {
        $string = "\n";
        for ($y = $this->getMinY(); $y <= $this->getMaxY(); $y++) {
            for ($x = $this->getMinX(); $x <= $this->getMaxX(); $x++) {
                $string .= $this->get(new Point2D($x, $y));
            }
            $string .= "\n";
        }

        return $string;
    }
}
