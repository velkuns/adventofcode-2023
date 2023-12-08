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

use function array_shift;
use function explode;
use function str_ends_with;
use function str_split;
use function trim;

class PuzzleDay8 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $instructions = $this->parseInstructions((string) array_shift($inputs));
        $network      = $this->parseMap($inputs);

        return $this->walkToTheEnd('AAA', clone $instructions, $network);
    }

    /**
     * @param \SplQueue<string> $instructions
     * @param array<string, array{L: string, R: string}> $network
     */
    private function walkToTheEnd(string $position, \SplQueue $instructions, array $network): int
    {
        $step = 0;
        //~ While the current position not ending with Z char, continue to progress following instructions
        while (!str_ends_with($position, 'Z')) {
            //~ Dequeue next instruction
            $instruction = $instructions->dequeue();

            //~ Increase step and get next position in network
            $step++;
            $position = $network[$position][$instruction];

            //~ Then re-queue instruction for later if needed
            $instructions->enqueue($instruction);
        }

        return $step;
    }

    /**
     * @return \SplQueue<string>
     */
    private function parseInstructions(string $data): \SplQueue
    {
        /** @var \SplQueue<string> $instructions */
        $instructions = new \SplQueue();

        foreach (str_split($data) as $instruction) {
            $instructions->enqueue($instruction);
        }

        return $instructions;
    }

    /**
     * @param list<string> $inputs
     * @return array<string, array{L: string, R: string}>
     */
    private function parseMap(array &$inputs): array
    {
        array_shift($inputs); // Skip empty line

        $network = [];
        foreach ($inputs as $string) {
            [$name, $nodes] = explode(' = ', $string);
            [$left, $right] = explode(', ', trim($nodes, '()'));

            $network[$name] = ['L' => $left, 'R' => $right];
        }

        return $network;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $instructions = $this->parseInstructions((string) array_shift($inputs));
        $network      = $this->parseMap($inputs);

        //~ Search for all node ending with char 'A'
        $positions = array_values(array_filter(array_keys($network), fn(string $node) => str_ends_with($node, 'A')));

        //~ For each starting position, get number of step to progress until the end
        $cycles = [];
        foreach ($positions as $index => $position) {
            $cycles[$index] = $this->walkToTheEnd($position, clone $instructions, $network);
        }

        $gcd = $this->greatCommonDivisor($cycles);
        return $this->leastCommonMultiple($cycles, $gcd);
    }

    /**
     * @param list<int> $cycles
     */
    private function greatCommonDivisor(array $cycles): int
    {
        $gcd = (int) array_shift($cycles);
        foreach ($cycles as $cycle) {
            $a = max($gcd, $cycle);
            $b = min($gcd, $cycle);
            $r = $a % $b;

            while ($r > 0) {
                $a = $b;
                $b = $r;
                $r = $a % $b;
            }

            $gcd = $b;
        }

        return $gcd;
    }

    /**
     * @param list<int> $cycles
     */
    private function leastCommonMultiple(array $cycles, int $gcd): int
    {
        $lcm = (int) array_shift($cycles);
        foreach ($cycles as $cycle) {
            $lcm = ($lcm * $cycle) / $gcd;
        }

        return (int) $lcm;
    }
}
