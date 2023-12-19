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

class PuzzleDay19 extends Puzzle
{
    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        [$workflows, $parts] = $this->parseInputs($inputs);

        $total = 0;
        foreach ($parts as $part) {
            $workflow = $workflows['in'];
            $result   = $this->applyWorkflow($part, $workflow);
            while (!in_array($result, ['A', 'R'])) {
                $workflow = $workflows[$result];
                $result   = $this->applyWorkflow($part, $workflow);
            }

            if ($result === 'A') {
                $total += array_sum($part);
            }
        }

        return $total;
    }

    /**
     * @param array{x: int, m:int, a: int, s: int} $part
     * @param list<array{category: string, operator: string, value: int, do: string}> $workflow
     * @return string
     */
    private function applyWorkflow(array $part, array $workflow): string
    {
        foreach ($workflow as $rule) {
            $result  = match ($rule['operator']) {
                '>' => $part[$rule['category']] > $rule['value'] ? $rule['do'] : null,
                '<' => $part[$rule['category']] < $rule['value'] ? $rule['do'] : null,
                default => $rule['do'],
            };

            if ($result !== null) {
                return $result;
            }
        }

        throw new \UnexpectedValueException('Invalid workflow: no rule has expected result!');
    }

    /**
     * @param list<string> $inputs
     * @return array{
     *     0: array<string, list<array{category: string, operator: string, value: int, do: string}>>,
     *     1: list<array{x: int, m:int, a: int, s: int}>
     * }
     */
    private function parseInputs(array $inputs): array
    {
        $parseWorkflows = true;
        $workflows      = [];
        $parts          = [];
        foreach ($inputs as $line) {
            $parseWorkflows = ($parseWorkflows && !empty($line));

            if (empty($line)) {
                continue;
            }

            if ($parseWorkflows) {
                $workflows = array_merge($workflows, $this->parseWorkflow($line));
            } else {
                $parts[] = $this->parsePart($line);
            }
        }

        return [$workflows, $parts];
    }

    /**
     * @return array<string, list<array{category: string, operator: string, value: int,do: string}>>
     */
    private function parseWorkflow(string $line): array
    {
        $pattern = '/((?<category>[xmas])(?<operator>[<>])(?<value>\d+):((?<do1>[a-z]+)|(?<finally1>[AR])))|((?<finally2>[AR])|(?<do2>[a-z]+))/';
        $name    = substr($line, 0, (int) strpos($line, '{'));
        $data    = substr($line, (int) strpos($line, '{') + 1, -1);

        $matches = [];
        $count   = preg_match_all($pattern, $data, $matches);

        $workflow = [];
        for ($i = 0; $i < $count; $i++) {
            if ($matches['operator'][$i] !== '') {
                $rule = [
                    'category' => (string) $matches['category'][$i],
                    'operator' => (string) $matches['operator'][$i],
                    'value'    => (int) $matches['value'][$i],
                    'do'       => $matches['finally1'][$i] ?: $matches['do1'][$i],
                ];
            } else {
                $rule = [
                    'category' => (string) $matches['category'][$i],
                    'operator' => (string) $matches['operator'][$i],
                    'value'    => (int) $matches['value'][$i],
                    'do'       => $matches['finally2'][$i] ?: $matches['do2'][$i],
                ];
            }

            $workflow[] = $rule;
        }

        return [$name => $workflow];
    }

    /**
     * @return array{x: int, m:int, a: int, s: int}
     */
    private function parsePart(string $line): array
    {
        $pattern = '/{x=(?<x>\d+),m=(?<m>\d+),a=(?<a>\d+),s=(?<s>\d+)}/';

        $matches = [];
        preg_match($pattern, $line, $matches);

        return [
            'x' => (int) $matches['x'],
            'm' => (int) $matches['m'],
            'a' => (int) $matches['a'],
            's' => (int) $matches['s'],
        ];
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        //~ Not doing part 2 because it not so funny to compute range in optimised way :P
        return 0;
    }
}
