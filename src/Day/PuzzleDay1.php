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
use Velkuns\Pipeline\PipelineArray;

class PuzzleDay1 extends Puzzle
{
    private const NUMBERS = [
        'one'   => '1',
        'two'   => '2',
        'three' => '3',
        'four'  => '4',
        'five'  => '5',
        'six'   => '6',
        'seven' => '7',
        'eight' => '8',
        'nine'  => '9',
    ];

    /**
     * @param list<string> $inputs
     */
    protected function partOne(array $inputs): int
    {
        $val = 0;
        foreach ($inputs as $input) {
            $code = \str_split((string) \preg_replace('`[a-z]+`', '', $input));
            $val  += (int) (\current($code) . \end($code));
        }

        return $val;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwo(array $inputs): int
    {
        $val = 0;
        foreach ($inputs as $input) {
            $numbers = [];
            \preg_match_all('`(?=(\d|one|two|three|four|five|six|seven|eight|nine))`', $input, $numbers);
            $numbers = \str_replace(\array_keys(self::NUMBERS), \array_values(self::NUMBERS), $numbers[1]);

            $val += (int) (\current($numbers) . \end($numbers));
        }

        return $val;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partOneFunctional(array $inputs): int
    {
        $val = (new PipelineArray($inputs))
            ->each()
                ->regex('replace', '`[a-z]+`', '')
                ->split(1)
            ->end()
            ->map(function (array $input): int {
                return (int) (new PipelineArray($input))
                    ->store('array')
                    ->first()
                    ->store('first')
                    ->retrieve('array')
                    ->last()
                    ->store('last')
                    ->retrieve('first', 'last')
                    ->implode()
                    ->get()
                ;
            })
            ->sum()
            ->get()
        ;

        return (int) $val;
    }

    /**
     * @param list<string> $inputs
     */
    protected function partTwoFunctional(array $inputs): int
    {
        $val = (new PipelineArray($inputs))
            ->each()
                ->regex('matchAll', '`(?=(\d|one|two|three|four|five|six|seven|eight|nine))`')
                ->value(1)
                ->replace(\array_keys(self::NUMBERS), \array_values(self::NUMBERS))
            ->end()
            ->map(function (array $input): int {
                //~ Use map instead of each, due to usage of store / retrieve
                return (int) (new PipelineArray($input))
                    ->store('array')
                    ->first()
                    ->store('first')
                    ->retrieve('array')
                    ->last()
                    ->store('last')
                    ->retrieve('first', 'last')
                    ->implode()
                    ->get()
                ;
            })
            ->sum()
            ->get()
        ;

        return (int) $val;
    }
}
