<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Matrix;

use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\Matrix;

class Platform extends Matrix
{
    public const ROUNDED_ROCK = 'O';
    public const CUBE_ROCK = '#';
    public const VOID = '.';

    public const NORTH = 'N';
    public const WEST  = 'W';
    public const SOUTH = 'S';
    public const EAST = 'E';


    /** @var Point2D $roundedRocks */
    private array $roundedRocks = [];

    /** @var Point2D $cubeRocks */
    private array $cubeRocks = [];

    /** @var array<int, array<int>> $stopPoints */
    private array $stopPoints = [];

    public function hash(): string
    {
        $string = '';
        foreach ($this->matrix as $line) {
            $string .= implode('', $line);
        }

        return hash('xxh64', $string);
    }

    public function init(): static
    {
        $this->cubeRocks    = $this->locateAll(Platform::CUBE_ROCK);
        $this->roundedRocks = $this->locateAll(Platform::ROUNDED_ROCK);

        return $this;
    }

    public function tiltOn(string $direction): static
    {
        $this->initStopPointsFor($direction);

        //~ For South & East direction, we need to reverse order to move correctly the rocks
        $roundedRocks = $this->sortRoundedRockFor($direction);

        $this->roundedRocks = [];
        foreach ($roundedRocks as $roundedRock) {
            $this->roundedRocks[] = match($direction) {
                Platform::NORTH => $this->moveRoundedRockOnNorth($roundedRock),
                Platform::SOUTH => $this->moveRoundedRockOnSouth($roundedRock),
                Platform::WEST  => $this->moveRoundedRockOnWest($roundedRock),
                Platform::EAST  => $this->moveRoundedRockOnEast($roundedRock),
                default         => throw new \UnexpectedValueException('Unknown direction'),
            };
        }

        //~ For South & East direction, we need to go back to original order, when all move are done
        $this->roundedRocks = $this->sortRoundedRockFor($direction);

        return $this;
    }

    public function getTotalLoad(): int
    {
        return array_reduce(
            $this->roundedRocks,
            fn(int $load, Point2D $roundedRock) => $load + ($this->height() - $roundedRock->getY()),
            0
        );
    }

    private function initStopPointsFor(string $direction): void
    {
        $this->stopPoints = [];
        foreach ($this->cubeRocks as $cubeRock) {
            if ($direction === Platform::NORTH || $direction === Platform::SOUTH) {
                $this->stopPoints[$cubeRock->getX()][] = $cubeRock->getY();
            } else {
                $this->stopPoints[$cubeRock->getY()][] = $cubeRock->getX();
            }
        }

        if ($direction === Platform::SOUTH || $direction === Platform::EAST) {
            foreach ($this->stopPoints as $n => $points) {
                rsort($points);
                $this->stopPoints[$n] = $points;
            }
        }
    }

    /**
     * @return Point2D
     */
    private function sortRoundedRockFor(string $direction): array
    {
        $roundedRocks = [];
        foreach ($this->roundedRocks as $roundedRock) {
            if ($direction === Platform::NORTH || $direction === Platform::SOUTH) {
                $roundedRocks[$roundedRock->getX()][$roundedRock->getY()] = $roundedRock;
            } else {
                $roundedRocks[$roundedRock->getY()][$roundedRock->getX()] = $roundedRock;
            }
        }

        foreach ($roundedRocks as $n => $data) {
            if ($direction === Platform::NORTH || $direction === Platform::WEST) {
                ksort($roundedRocks[$n]);
            } else {

                krsort($roundedRocks[$n]);
            }
        }
        ksort($roundedRocks);

        return array_merge(...$roundedRocks);
    }

    public function moveRoundedRockOnNorth(Point2D $roundedRock): Point2D
    {
        //~ Already on limit, so return it
        if ($roundedRock->getY() === $this->getMinY()) {
            //~ Mark current location of the rock as stop point
            $this->stopPoints[$roundedRock->getX()][] = $roundedRock->getY();
            sort($this->stopPoints[$roundedRock->getX()]); // Make sure the stop points are sorted
            return $roundedRock;
        }

        $moveToY = $this->getMinY();

        //~ Check for any stop point on north direction
        foreach ($this->stopPoints[$roundedRock->getX()] ?? [] as $y) {
            //~ Rounded rock is forward the stop point, so stop searching for stop point
            if ($roundedRock->getY() < $y) {
                break;
            }

            //~ Have stop points far away. Store possible position, and continue to look closer.
            $moveToY = $y + 1;
        }

        //~ Then create new position for rounded rock, replace by '.' and move rounded rock to new position.
        $newPosition = new Point2D($roundedRock->getX(), $moveToY);
        $this
            ->set($roundedRock, self::VOID)
            ->set($newPosition, self::ROUNDED_ROCK)
        ;

        //~ Add stop point at the final place of rounded rock (that will stop further rounded rock from south)
        $this->stopPoints[$newPosition->getX()][] = $newPosition->getY();
        sort($this->stopPoints[$roundedRock->getX()]); // Make sure the stop points are sorted

        return $newPosition;
    }

    public function moveRoundedRockOnSouth(Point2D $roundedRock): Point2D
    {
        //~ Already on limit, so return it
        if ($roundedRock->getY() === $this->getMaxY()) {
            //~ Mark current location of the rock as stop point
            $this->stopPoints[$roundedRock->getX()][] = $roundedRock->getY();
            rsort($this->stopPoints[$roundedRock->getX()]); // Make sure the stop points are sorted
            return $roundedRock;
        }

        $moveToY = $this->getMaxY();

        //~ Check for any stop point on north direction
        foreach ($this->stopPoints[$roundedRock->getX()] ?? [] as $y) {
            //~ Rounded rock is forward the stop point, so stop searching for stop point
            if ($roundedRock->getY() > $y) {
                break;
            }

            //~ Have stop points far away. Store possible position, and continue to look closer.
            $moveToY = $y - 1;
        }

        //~ Then create new position for rounded rock, replace by '.' and move rounded rock to new position.
        $newPosition = new Point2D($roundedRock->getX(), $moveToY);
        $this
            ->set($roundedRock, self::VOID)
            ->set($newPosition, self::ROUNDED_ROCK)
        ;

        //~ Add stop point at the final place of rounded rock (that will stop further rounded rock from north)
        $this->stopPoints[$newPosition->getX()][] = $newPosition->getY();
        rsort($this->stopPoints[$roundedRock->getX()]); // Make sure the stop points are sorted

        return $newPosition;
    }

    public function moveRoundedRockOnWest(Point2D $roundedRock): Point2D
    {
        //~ Already on limit, so return it
        if ($roundedRock->getX() === $this->getMinX()) {
            //~ Mark current location of the rock as stop point
            $this->stopPoints[$roundedRock->getY()][] = $roundedRock->getX();
            sort($this->stopPoints[$roundedRock->getY()]); // Make sure the stop points are sorted
            return $roundedRock;
        }

        $moveToX = $this->getMinX();

        //~ Check for any stop point on West direction
        foreach ($this->stopPoints[$roundedRock->getY()] ?? [] as $x) {
            //~ Rounded rock is forward the stop point, so stop searching for stop point
            if ($roundedRock->getX() < $x) {
                break;
            }

            //~ Have stop points far away. Store possible position, and continue to look closer.
            $moveToX = $x + 1;
        }

        //~ Then create new position for rounded rock, replace by '.' and move rounded rock to new position.
        $newPosition = new Point2D($moveToX, $roundedRock->getY());
        $this
            ->set($roundedRock, self::VOID)
            ->set($newPosition, self::ROUNDED_ROCK)
        ;

        //~ Add stop point at the final place of rounded rock (that will stop further rounded rock from east)
        $this->stopPoints[$newPosition->getY()][] = $newPosition->getX();
        sort($this->stopPoints[$roundedRock->getY()]); // Make sure the stop points are sorted

        return $newPosition;
    }

    public function moveRoundedRockOnEast(Point2D $roundedRock): Point2D
    {
        //~ Already on limit, so return it
        if ($roundedRock->getX() === $this->getMaxX()) {
            //~ Mark current location of the rock as stop point
            $this->stopPoints[$roundedRock->getY()][] = $roundedRock->getX();
            rsort($this->stopPoints[$roundedRock->getY()]); // Make sure the stop points are sorted
            return $roundedRock;
        }

        $moveToX = $this->getMaxX();

        //~ Check for any stop point on north direction
        foreach ($this->stopPoints[$roundedRock->getY()] ?? [] as $x) {
            //~ Rounded rock is forward the stop point, so stop searching for stop point
            if ($roundedRock->getX() > $x) {
                break;
            }

            //~ Have stop points far away. Store possible position, and continue to look closer.
            $moveToX = $x - 1;
        }

        //~ Then create new position for rounded rock, replace by '.' and move rounded rock to new position.
        $newPosition = new Point2D($moveToX, $roundedRock->getY());
        $this
            ->set($roundedRock, self::VOID)
            ->set($newPosition, self::ROUNDED_ROCK)
        ;

        //~ Add stop point at the final place of rounded rock (that will stop further rounded rock from West)
        $this->stopPoints[$newPosition->getY()][] = $newPosition->getX();
        rsort($this->stopPoints[$roundedRock->getY()]); // Make sure the stop points are sorted

        return $newPosition;
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
