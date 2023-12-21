<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Matrix\Garden;
use Application\Puzzle;
use Application\Service\Parser;

class PuzzleDay21 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        [$nbStep,] = explode(',', (string) array_shift($inputs)); // I added n,m nb steps for each part
        $garden = Parser::toMatrix($inputs, Garden::class);
        $garden->computeAllShortestPaths($garden->locate('S'));

        return $garden->countTilesReachableInMaxSteps((int) $nbStep, false);
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        [,$nbStep] = explode(',', (string) array_shift($inputs)); // I added n,m nb steps for each part
        $garden = Parser::toMatrix($inputs, Garden::class);
        $garden->computeAllShortestPaths($garden->locate('S'));

        return $garden->countTilesReachableInMaxSteps((int) $nbStep, true);
    }
}
