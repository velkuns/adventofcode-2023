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
use Application\Service\Map;
use Application\Service\Parser;
use Velkuns\Math\_2D\Point2D;

class PuzzleDay17 extends Puzzle
{
    /**
     * 859
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $map = Parser::toMatrix($inputs, Map::class, true);

        $start = new Point2D($map->getMinX(), $map->getMinY());
        $end   = new Point2D($map->getMaxX(), $map->getMaxY());

        return $map->findColdestPath($start, $end, 1, 3);
    }

    /**
     * 1018 to low :(
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $map = Parser::toMatrix($inputs, Map::class, true);

        $start = new Point2D($map->getMinX(), $map->getMinY());
        $end   = new Point2D($map->getMaxX(), $map->getMaxY());

        return $map->findColdestPath($start, $end, 4, 10);
    }
}
