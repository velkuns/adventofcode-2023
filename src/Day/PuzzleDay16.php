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
use Application\Service\Parser;
use PhpParser\Node\Scalar\MagicConst\Dir;
use Velkuns\Math\_2D\Direction;
use Velkuns\Math\_2D\Point2D;
use Velkuns\Math\_2D\Vector2DDir;
use Velkuns\Math\Matrix;

class PuzzleDay16 extends Puzzle
{
    private const EMPTY = '.';
    private const MIRROR_01 = '/';
    private const MIRROR_02 = '\\';
    private const SPLITTER_HORIZONTAL = '-';
    private const SPLITTER_VERTICAL = '|';

    private const MAP_DIR_TILE = [
        Direction::Up->value => [
            self::MIRROR_01           => [Direction::Right],
            self::MIRROR_02           => [Direction::Left],
            self::SPLITTER_HORIZONTAL => [Direction::Right, Direction::Left],
            self::SPLITTER_VERTICAL   => [Direction::Up],
        ],
        Direction::Down->value => [
            self::MIRROR_01           => [Direction::Left],
            self::MIRROR_02           => [Direction::Right],
            self::SPLITTER_HORIZONTAL => [Direction::Right, Direction::Left],
            self::SPLITTER_VERTICAL   => [Direction::Down],
        ],
        Direction::Right->value => [
            self::MIRROR_01           => [Direction::Up],
            self::MIRROR_02           => [Direction::Down],
            self::SPLITTER_HORIZONTAL => [Direction::Right],
            self::SPLITTER_VERTICAL   => [Direction::Up, Direction::Down],
        ],
        Direction::Left->value => [
            self::MIRROR_01           => [Direction::Down],
            self::MIRROR_02           => [Direction::Up],
            self::SPLITTER_HORIZONTAL => [Direction::Left],
            self::SPLITTER_VERTICAL   => [Direction::Up, Direction::Down],
        ],
    ];

    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $contraption = Parser::toMatrix($inputs);

        $start     = new Point2D(0, 0);
        $direction = Direction::Right;

        return $this->getNumberOfEnergizedTilesFromStartPosition($contraption, $start, $direction);
    }

    private function getNumberOfEnergizedTilesFromStartPosition(
        Matrix $contraption,
        Point2D $start,
        Direction $direction
    ): int {
        $energizedTiles = [];

        /** @var \SplQueue<array{0: Point2D, 1: Direction}> $beams */
        $beams = new \SplQueue();
        $beams->enqueue([$start, $direction]);

        while (!$beams->isEmpty()) {
            //~ Dequeue a beam from the queue
            /** @var Point2D $position */
            [$position, $direction] = $beams->dequeue();

            /** @var string|null $tile */
            $tile = $contraption->get($position);

            //~ This position is out of contraption ?
            if ($tile === null) {
                continue; // Yes, so skip and go to the next beam
            }

            //~ Have already the beam in that direction registered ?
            if (isset($energizedTiles[(string) $position][$direction->value])) {
                continue; // Yes, so skip next step, because we already process the beam path
            }

            //~ Not yet. So save energized tile (for that direction) in list
            $energizedTiles[(string) $position][$direction->value] = 1;

            foreach ($this->nextDirections($direction, $tile) as $nextDirection) {
                $nextPosition = $position->translate(Vector2DDir::fromDirection($nextDirection, invertY: true));
                $beams->enqueue([$nextPosition, $nextDirection]);
            }
        }

        return count($energizedTiles);
    }

    /**
     * @param Direction $direction
     * @param string $tile
     * @return list<Direction>
     */
    private function nextDirections(Direction $direction, string $tile): array
    {
        //~ continue on same direction
        if ($tile === self::EMPTY) {
            return [$direction];
        }

        //~ Otherwise, use cache map to get next directions
        return self::MAP_DIR_TILE[$direction->value][$tile];
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $contraption = Parser::toMatrix($inputs);

        $sides = [
            'from_left'   => [Direction::Right, $contraption->getMinY(), $contraption->getMaxY()],
            'from_right'  => [Direction::Left, $contraption->getMinY(), $contraption->getMaxY()],
            'from_top'    => [Direction::Down, $contraption->getMinX(), $contraption->getMaxX()],
            'from_bottom' => [Direction::Up, $contraption->getMinX(), $contraption->getMaxX()],
        ];

        $energies = [];
        foreach ($sides as [$direction, $min, $max]) {
            for ($i = $min; $i < $max; $i++) {
                $start      = $this->getStartingPoint($contraption, $direction, $i);
                $energies[] = $this->getNumberOfEnergizedTilesFromStartPosition($contraption, $start, $direction);
            }
        }

        return max($energies);
    }

    private function getStartingPoint(Matrix $contraption, Direction $direction, int $index): Point2D
    {
        $x = match($direction) {
            Direction::Left => $contraption->getMaxX(),  // Go left, so start from right side
            Direction::Right => $contraption->getMinX(), // Go right, so start from left side
            Direction::Up, Direction::Down => $index,
        };


        $y = match($direction) {
            Direction::Up   => $contraption->getMaxY(), // Go up, then start from bottom
            Direction::Down => $contraption->getMinY(), // Go down, then start from top
            Direction::Right, Direction::Left => $index,
        };

        return new Point2D($x, $y);
    }
}
