<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Day;

use Application\Matrix\Pattern;
use Application\Puzzle;
use Application\Service\Cache;
use Velkuns\Math\_2D\Point2D;

class PuzzleDay13 extends Puzzle
{
    /** @var Cache<int> $cache*/
    private Cache $cache;

    /**
     * 35232 :)
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        //~ Init cache
        $this->cache = new Cache();

        $value       = 0;
        $patterns    = $this->parsePatterns($inputs);

        foreach ($patterns as $index => $pattern) {
            $value += $this->getValue($pattern, $index, true);
        }

        return $value;
    }

    /**
     * 35152 > too low :(
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $value    = 0;
        $patterns = $this->parsePatterns($inputs);

        foreach ($patterns as $index => $pattern) {

            //~ For each pattern, try to fix it by switching the char at position (x,y).
            for ($x = $pattern->getMinX(); $x <= $pattern->getMaxX(); $x++) {
                for ($y = $pattern->getMinY(); $y <= $pattern->getMaxY(); $y++) {

                    //~ Fix pattern by switching a char at (x,y)
                    $tryPattern = $this->fixPattern($pattern, $x, $y);

                    //~ Get value from fixed pattern. If > 0, it a new one.
                    //~ Otherwise, it the same reflection point or no reflection point.
                    $tryValue   = $this->getValue($tryPattern, $index, false);

                    //~ Found a new reflection point, so add value & continue to next pattern
                    if ($tryValue > 0) {
                        $value += $tryValue;
                        continue 3;
                    }
                }
            }
        }

        return $value;
    }

    private function getValue(Pattern $pattern, int $index, bool $storeInCache): int
    {
        $verticalReflectionPoint   = $pattern->getVerticalReflectionPoint($index . 'V', $storeInCache);
        $horizontalReflectionPoint = $pattern->getHorizontalReflectionPoint($index . 'H', $storeInCache);

        return $verticalReflectionPoint + ($horizontalReflectionPoint * 100);
    }

    private function fixPattern(Pattern $pattern, int $x, int $y): Pattern
    {
        $point        = new Point2D($x, $y);
        $char         = $pattern->get($point);
        $fixedPattern = clone $pattern;
        $fixedPattern->set($point, $char === '.' ? '#' : '.');

        return $fixedPattern;
    }

    /**
     * @param list<string> $inputs
     * @return list<\Application\Matrix\Pattern>
     */
    private function parsePatterns(array $inputs): array
    {
        $patterns = [];
        $data     = [];

        foreach ($inputs as $input) {
            if (empty($input)) {
                $patterns[] = (new Pattern(array_map(str_split(...), $data)))->transpose()->setCache($this->cache);
                $data       = [];
                continue;
            }

            $data[] = $input;
        }

        if (!empty($data)) {
            $patterns[] = (new Pattern(array_map(str_split(...), $data)))->transpose()->setCache($this->cache);
        }

        return $patterns;
    }
}
